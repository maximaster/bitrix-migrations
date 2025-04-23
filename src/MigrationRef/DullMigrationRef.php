<?php

declare(strict_types=1);

namespace Maximaster\BitrixMigrations\MigrationRef;

use Doctrine\Migrations\AbstractMigration;

enum DullMigrationRef: string implements MigrationRef
{
    use MigrationRefTrait;

    case TODO = AbstractMigration::class;

    public static function allSubqueryParameters(): array
    {
        return [];
    }
}
