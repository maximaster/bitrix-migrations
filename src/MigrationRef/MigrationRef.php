<?php

declare(strict_types=1);

namespace Maximaster\BitrixMigrations\MigrationRef;

use Closure;
use Maximaster\BitrixMigrations\BitrixMigration;
use Maximaster\BitrixMigrations\Sql\Parameters;

interface MigrationRef
{
    public const TODO = DullMigrationRef::TODO;

    /**
     * Получить значение первичного ключа объекта, который создала миграция.
     *
     * @return non-empty-string
     */
    public function primaryId(BitrixMigration $migration): string;

    /**
     * Получить подзапрос для получения идентификатора объекта, который создала миграция.
     *
     * @return Closure(Parameters):string
     */
    public function idSubquery(): Closure;
}
