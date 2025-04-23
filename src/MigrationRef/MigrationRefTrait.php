<?php

namespace Maximaster\BitrixMigrations\MigrationRef;

use Closure;
use Maximaster\BitrixMigrations\BitrixMigration;
use Maximaster\BitrixMigrations\Sql\Parameters;
use RuntimeException;

trait MigrationRefTrait
{
    /**
     * @return array<non-empty-string, array{non-empty-string, non-empty-string, non-empty-string}>
     */
    abstract protected static function allSubqueryParameters(): array;

    /**
     * @return array{non-empty-string, non-empty-string, non-empty-string}
     */
    private function getSubqueryParameters(): array
    {
        return self::allSubqueryParameters()[$this->value]
            ?? throw new RuntimeException(sprintf('Укажите данные в константе ID_SUBQUERY_PARAMETERS для %s.', $this->name));
    }

    /**
     * Получить значение первичного ключа объекта, который создала миграция.
     *
     * @return non-empty-string
     */
    public function primaryId(BitrixMigration $migration): string
    {
        [$table, $queryField, $fieldValue] = $this->getSubqueryParameters();

        return $migration->idWhere($table, "$queryField = ?", [$fieldValue]);
    }

    /**
     * Получить подзапрос для получения идентификатора объекта, который создала миграция.
     *
     * @return Closure(Parameters):string
     */
    public function idSubquery(): Closure
    {
        [$table, $queryField, $fieldValue] = $this->getSubqueryParameters();

        return static fn (Parameters $_) => "(SELECT ID FROM $table WHERE {$_->allLike([$queryField => $fieldValue])})";
    }
}
