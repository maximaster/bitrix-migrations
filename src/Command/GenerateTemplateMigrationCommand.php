<?php

declare(strict_types=1);

namespace Maximaster\BitrixMigrations\Command;

use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\DoctrineCommand;
use Exception;
use Maximaster\Attributemplate\Contract\Context;
use Maximaster\Attributemplate\Contract\FileFactory;
use Maximaster\Attributemplate\Contract\TemplateParameterCollector;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Генерирует миграцию по проектному шаблону.
 */
class GenerateTemplateMigrationCommand extends DoctrineCommand
{
    public const DEFAULT_NAME = 'bitrix-migrations:generate-template-migration';
    protected static string $defaultName = self::DEFAULT_NAME;

    public const ARG_TEMPLATE = 'template';
    public const ARG_CONTEXT = 'context';
    public const OPT_NAMESPACE = 'namespace';
    public const OPT_NO_INTERATION = 'no-interaction';

    private const DEFAULT_MIGRATION_NAMESPACE = 'ProjectMigration';
    private const MIGRATION_NAME_PARAMETER = 'MIGRATION_NAME';

    public function __construct(
        private FileFactory $fileFactory,
        private TemplateParameterCollector $parameterCollector,
        ?DependencyFactory $dependencyFactory = null,
        ?string $name = self::DEFAULT_NAME,
    ) {
        parent::__construct($dependencyFactory, $name);
    }

    protected function configure(): void
    {
        $this->addArgument(self::ARG_TEMPLATE, InputArgument::REQUIRED, 'Путь к файлу шаблона.');
        $this->addArgument(
            self::ARG_CONTEXT,
            InputArgument::OPTIONAL,
            'Контекст с данными, кодированными как параметры URL.'
        );
        $this->addOption(
            self::OPT_NAMESPACE,
            null,
            InputOption::VALUE_REQUIRED,
            'The namespace to use for the migration (must be in the list of configured namespaces)',
            self::DEFAULT_MIGRATION_NAMESPACE,
        );
        $this->addOption(
            self::OPT_NO_INTERATION,
            'n',
            InputOption::VALUE_NONE,
            'Не задавать вопросы, включая вопросы про значения параметров.'
        );
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ss = new SymfonyStyle($input, $output);

        $context = $this->parseContext($input);

        $templateFile = $input->getArgument(self::ARG_TEMPLATE);
        $templateFile = is_scalar($templateFile) ? strval($templateFile) : '';

        $namespace = $input->getOption(self::OPT_NAMESPACE);
        $namespace = is_scalar($namespace) ? strval($namespace) : '';

        $noInteractions = boolval($input->getOption(self::OPT_NO_INTERATION));

        $di = $this->getDependencyFactory();
        $this->normalizeNamespace($namespace, $di->getConfiguration());
        $migrationFqcn = $di->getClassNameGenerator()->generateClassName($namespace);
        $migrationPath = $di->getMigrationGenerator()->generateMigration($migrationFqcn);

        $context[self::MIGRATION_NAME_PARAMETER] = basename($migrationPath, '.php');
        $context = $this->collectParameters($ss, $templateFile, $context, !$noInteractions);

        $migrationCode = $this->generateMigrationCode($templateFile, $context, $namespace);
        $this->saveMigration($ss, $migrationPath, $migrationCode);

        $ss->success(sprintf('Файл миграции успешно создан: %s', $migrationPath));

        return self::SUCCESS;
    }

    /**
     * @return array<non-empty-string, array<mixed>|string>
     */
    private function parseContext(InputInterface $input): array
    {
        $context = [];
        $contextArg = $input->getArgument(self::ARG_CONTEXT) ?? '';
        parse_str(trim(is_scalar($contextArg) ? strval($contextArg) : ''), $parsed);

        foreach ($parsed as $key => $value) {
            if (is_string($key) && $key !== '') {
                $context[$key] = is_array($value) ? $value : strval($value);
            }
        }

        return $context;
    }

    /**
     * @param array<non-empty-string, mixed> $context
     * @return array<non-empty-string, mixed>
     */
    private function collectParameters(SymfonyStyle $ss, string $templateFile, array $context, bool $interactionsAllowed): array
    {
        $parameters = $this->parameterCollector->collect($templateFile);
        $hasSomeParameters = count($parameters) > 0;
        $shouldAskParameters = $interactionsAllowed
            && (
                $hasSomeParameters
                && boolval(
                    $ss->askQuestion(new ConfirmationQuestion('Уточните значения параметров в интерактивном режиме?'))
                )
            );

        foreach ($parameters as [$parameter, $parameterDefaultValue]) {
            $autoValue = $context[$parameter->name] ?? $parameterDefaultValue;
            if (!$shouldAskParameters) {
                $context[$parameter->name] = $autoValue;
                continue;
            }

            $answer = $ss->askQuestion(new Question($parameter->name, is_scalar($autoValue) || is_null($autoValue) ? $autoValue : null));
            $context[$parameter->name] = is_string($answer) && $answer !== '' ? mb_convert_encoding($answer, 'UTF-8', 'UTF-8') : $answer;
        }
        return $context;
    }

    /**
     * @param array<non-empty-string, mixed> $context
     * @return string
     */
    private function generateMigrationCode(string $templateFile, array $context, string $namespace): string
    {
        /** @var class-string $templateFile */
        $migrationCode = $this->fileFactory->create($templateFile, new Context($context));

        return $this->syncNamespace($migrationCode, $namespace);
    }

    private function saveMigration(SymfonyStyle $ss, string $migrationPath, string $migrationCode): void
    {
        if (file_put_contents($migrationPath, $migrationCode) === false) {
            $ss->error(sprintf('Не удалось сохранить миграцию в файл %s.', $migrationPath));
        }
    }

    /**
     * @throws Exception
     */
    private function normalizeNamespace(string &$namespace, Configuration $configuration): void
    {
        if ($namespace === '') {
            $namespace = key($configuration->getMigrationDirectories());
        } elseif (isset($configuration->getMigrationDirectories()[$namespace]) === false) {
            throw new Exception(sprintf('Неизвестный namespace %s. Не создана директория миграций?', $namespace));
        }
    }

    /**
     * @return string
     */
    private function syncNamespace(string $migrationCode, string $namespace): string
    {
        return preg_replace('~^namespace\s+\K[^;]+(?=;)~m', $namespace, $migrationCode) ?? $namespace;
    }
}
