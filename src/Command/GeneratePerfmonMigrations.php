<?php

declare(strict_types=1);

namespace Maximaster\BitrixMigrations\Command;

use Bitrix\Main\Loader;
use CPerfomanceSQL;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\DoctrineCommand;
use Doctrine\SqlFormatter\SqlFormatter;
use Maximaster\BitrixLoader\BitrixLoader;
use Maximaster\BitrixMigrations\NamespaceNormalizer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GeneratePerfmonMigrations extends DoctrineCommand
{
    public const OPT_NAMESPACE = 'namespace';

    private BitrixLoader $bitrixLoader;
    private SqlFormatter $sqlFormatter;
    private NamespaceNormalizer $namespaceNormalizer;

    public static function getDefaultName(): ?string
    {
        return 'bitrix-migrations:generate-perfmon';
    }

    public function __construct(
        BitrixLoader $bitrixLoader,
        SqlFormatter $sqlFormatter,
        NamespaceNormalizer $namespaceNormalizer,
        ?DependencyFactory $dependencyFactory = null,
        ?string $name = null
    ) {
        parent::__construct($dependencyFactory, $name);

        $this->bitrixLoader = $bitrixLoader;
        $this->sqlFormatter = $sqlFormatter;
        $this->namespaceNormalizer = $namespaceNormalizer;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Сгенерировать миграцию по запросам собранным монитором производительности.')
            ->addOption(
                self::OPT_NAMESPACE,
                null,
                InputOption::VALUE_REQUIRED,
                'The namespace to use for the migration (must be in the list of configured namespaces)',
            );

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ss = new SymfonyStyle($input, $output);

        $this->bitrixLoader->prologBefore();
        if (Loader::includeModule('perfmon') === false) {
            $ss->error('Установите модуль perfmon.');

            return self::FAILURE;
        }

        $di = $this->getDependencyFactory();

        $namespace = $input->getOption(self::OPT_NAMESPACE);
        $namespace = is_scalar($namespace) ? strval($namespace) : '';
        $namespace = $this->namespaceNormalizer->normalize(
            $namespace,
            $di->getConfiguration()
        );

        $queries = [];
        $res = CPerfomanceSQL::GetList(
            ['SQL_TEXT'],
            [],
            ['NN' => 'ASC'],
            false
        );
        while ($entry = $res->Fetch()) {
            if (is_scalar($entry['SQL_TEXT']) === false) {
                continue;
            }

            $sql = trim(strval($entry['SQL_TEXT']));
            if ($this->shouldBeIncluded($sql) === false) {
                continue;
            }

            /** @var non-empty-string $formattedSql */
            $formattedSql = $this->sqlFormatter->format($sql);
            $queries[] = $formattedSql;
        }

        $migrationPath = $di->getMigrationGenerator()->generateMigration(
            $di->getClassNameGenerator()->generateClassName($namespace),
            $this->formatUp($queries),
        );

        $ss->success(sprintf('Успешна создана миграция по данным монитора производительности: %s', $migrationPath));

        return self::SUCCESS;
    }

    private function shouldBeIncluded(string $sql): bool
    {
        static $typeSliceLength = 9; // strlen('TRUNCATE') + 1;
        static $trackedTypes = [
            'CREATE',
            'DROP',
            'ALTER',
            'TRUNCATE',
            'COMMENT',
            'RENAME',
            'INSERT',
            'UPDATE',
            'DELETE',
        ];

        $currentType = mb_strtoupper(mb_substr($sql, 0, $typeSliceLength));
        foreach ($trackedTypes as $trackedType) {
            if (str_starts_with($currentType, $trackedType)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<non-empty-string> $queries
     */
    private function formatUp(array $queries): string
    {
        $up = [];
        foreach ($queries as $query) {
            $up[] = sprintf('$this->addSql(%s);', PHP_EOL . "<<<'MIGRATION_SQL'" . PHP_EOL . $query . PHP_EOL . "MIGRATION_SQL");
        }

        return implode(PHP_EOL, $up);
    }
}
