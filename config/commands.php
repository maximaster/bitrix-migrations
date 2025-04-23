<?php

declare(strict_types=1);

use Doctrine\SqlFormatter\NullHighlighter;
use Doctrine\SqlFormatter\SqlFormatter;
use Maximaster\Attributemplate\FileFactory\NetteFileFactory;
use Maximaster\Attributemplate\FileTraversal\NetteTemplateTraverser;
use Maximaster\Attributemplate\Nette\ClassUsageCollector;
use Maximaster\Attributemplate\Nette\NamespaceAttributeCleaner;
use Maximaster\Attributemplate\TemplateAttributeApplier\ClassConstantSetValueApplier;
use Maximaster\Attributemplate\TemplateAttributeApplier\ClassRenameApplier;
use Maximaster\Attributemplate\TemplateAttributeApplier\CompositeTemplateAttributeApplier;
use Maximaster\Attributemplate\TemplateParameterCollector\ReflectionTemplateParameterCollector;
use Maximaster\BitrixLoader\BitrixLoader;
use Maximaster\BitrixMigrations\Command\GeneratePerfmonMigrations;
use Maximaster\BitrixMigrations\Command\GenerateTableMigrationCliCommand;
use Maximaster\BitrixMigrations\Command\GenerateTemplateMigrationCommand;
use Maximaster\BitrixMigrations\NamespaceNormalizer;

return static fn (BitrixLoader $bitrixLoader) => [
    new GenerateTableMigrationCliCommand($bitrixLoader, new NamespaceNormalizer()),
    new GeneratePerfmonMigrations($bitrixLoader, new SqlFormatter(new NullHighlighter()), new NamespaceNormalizer()),
    new GenerateTemplateMigrationCommand(
        new NetteFileFactory(
            new NetteTemplateTraverser(),
            new NamespaceAttributeCleaner(new ClassUsageCollector()),
            new CompositeTemplateAttributeApplier([
                new ClassConstantSetValueApplier(),
                new ClassRenameApplier(),
            ]),
        ),
        new ReflectionTemplateParameterCollector(new NetteTemplateTraverser()),
    ),
];
