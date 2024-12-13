<?php

declare(strict_types=1);

use Doctrine\SqlFormatter\NullHighlighter;
use Doctrine\SqlFormatter\SqlFormatter;
use Maximaster\BitrixLoader\BitrixLoader;
use Maximaster\BitrixMigrations\Command\GeneratePerfmonMigrations;
use Maximaster\BitrixMigrations\Command\GenerateTableMigrationCliCommand;
use Maximaster\BitrixMigrations\NamespaceNormalizer;

return static fn (BitrixLoader $bitrixLoader) => [
    new GenerateTableMigrationCliCommand($bitrixLoader, new NamespaceNormalizer()),
    new GeneratePerfmonMigrations($bitrixLoader, new SqlFormatter(new NullHighlighter()), new NamespaceNormalizer()),
];
