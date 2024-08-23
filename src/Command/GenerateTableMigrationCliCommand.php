<?php

declare(strict_types=1);

namespace Maximaster\BitrixMigrations\Command;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\SystemException;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\DoctrineCommand;
use Exception;
use Maximaster\BitrixLoader\BitrixLoader;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Генерирует миграцию по классу таблицы (DataManager).
 */
class GenerateTableMigrationCliCommand extends DoctrineCommand
{
    public const ARG_TABLE = 'table';
    public const OPT_NAMESPACE = 'namespace';

    private BitrixLoader $bitrixLoader;

    public function __construct(
        BitrixLoader $bitrixLoader,
        ?DependencyFactory $dependencyFactory = null,
        ?string $name = null
    ) {
        parent::__construct($dependencyFactory, $name);
        $this->bitrixLoader = $bitrixLoader;
    }

    public static function getDefaultName(): ?string
    {
        return 'bitrix-migrations:generate-table';
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Генерирует миграцию которая создаёт таблицу в базе данных по Table-классу.')
            ->addArgument(
                self::ARG_TABLE,
                InputArgument::REQUIRED,
                'Полное имя Table-класса для которого нужно сгенерировать миграцию',
            )
            ->addOption(
                self::OPT_NAMESPACE,
                null,
                InputOption::VALUE_REQUIRED,
                'The namespace to use for the migration (must be in the list of configured namespaces)',
            );

        parent::configure();
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ss = new SymfonyStyle($input, $output);

        $di = $this->getDependencyFactory();

        $namespace = (string) $input->getOption(self::OPT_NAMESPACE);
        $this->normalizeNamespace($namespace, $di->getConfiguration());

        $this->bitrixLoader->prologBefore();

        $tableClass = $input->getArgument(self::ARG_TABLE);

        $createDump = [];
        foreach ($this->generateCreateTableDump($tableClass) as $query) {
            $createDump[] = sprintf('$this->addSql(%s);', var_export($query, true));
        }

        $migrationPath = $di->getMigrationGenerator()->generateMigration(
            $di->getClassNameGenerator()->generateClassName($namespace),
            implode("\n", $createDump),
            sprintf('$this->addSql(%s);', var_export($this->generateDropTableSql($tableClass), true))
        );

        $ss->success(
            sprintf(
                'Миграция для таблицы %s успешно создана: %s',
                var_export($tableClass, true),
                var_export($migrationPath, true)
            )
        );

        return 0;
    }

    /**
     * @throws Exception
     */
    private function normalizeNamespace(string &$namespace, Configuration $configuration): void
    {
        if ($namespace === '') {
            $namespace = null;
        }

        $dirs = $configuration->getMigrationDirectories();
        if ($namespace === null) {
            $namespace = key($dirs);
        } elseif (isset($dirs[$namespace]) === false) {
            throw new Exception(sprintf('Неизвестный namespace %s. Не создана директория миграций?', $namespace));
        }
    }

    /**
     * @return string[]
     *
     * @throws ArgumentException|SystemException
     *
     * @psalm-param class-string<DataManager> $tableClass
     */
    private function generateCreateTableDump(string $tableClass): array
    {
        /** @var DataManager $tableClass */
        return $tableClass::getEntity()->compileDbTableStructureDump();
    }

    /**
     * @psalm-param class-string<DataManager> $tableClass
     */
    private function generateDropTableSql(string $tableClass): string
    {
        /** @var DataManager $tableClass */
        return sprintf('DROP TABLE `%s`', $tableClass::getTableName());
    }
}
