<?php

declare(strict_types=1);

use Maximaster\BitrixLoader\BitrixLoader;
use Maximaster\BitrixMigrations\Command\GenerateTableMigrationCliCommand;

return static fn (BitrixLoader $bitrixLoader) => [
    new GenerateTableMigrationCliCommand($bitrixLoader)
];
