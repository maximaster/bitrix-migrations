<?php

declare(strict_types=1);

namespace Maximaster\BitrixMigrations;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\Migrations\AbstractMigration;
use InvalidArgumentException;
use Maximaster\BitrixMigrations\Exception\Exception as LibraryException;
use Maximaster\BitrixMigrations\Sql\Parameters;

/**
 * Базовый класс миграции для Bitrix проекта.
 *
 * @SuppressWarnings(PHPMD.CamelCaseVariableName) why:intended
 * @SuppressWarnings(PHPMD.NumberOfChildren) why:intended
 */
abstract class BitrixMigration extends AbstractMigration
{
    /**
     * Вернуть идентификатор записи таблицы для указанного условия.
     *
     * @throws DbalException
     * @throws LibraryException
     *
     * @psalm-param list<mixed>|array<string, mixed> $params
     * @psalm-return non-empty-string
     */
    protected function idWhere(string $table, string $where, array $params = []): string
    {
        return (string) $this->fieldWhere($table, 'ID', $where, $params);
    }

    /**
     * @psalm-return non-empty-string
     *
     * @throws LibraryException
     * @throws DbalException
     */
    protected function stringFieldWhere(string $table, string $field, string $where, array $params): string
    {
        $id = $this->fieldWhere($table, $field, $where, $params);
        if (is_string($id) === false || strlen($id) === 0) {
            throw new LibraryException(
                sprintf('Ожидалось получение не пустой строки, получено: %s', get_debug_type($id))
            );
        }

        return $id;
    }

    /**
     * Вернуть значение поля записи таблицы для указанного условия.
     *
     * @throws DbalException
     * @throws LibraryException
     *
     * @psalm-param list<mixed>|array<string, mixed> $params
     */
    protected function fieldWhere(string $table, string $field, string $where, array $params = [])
    {
        $ids = $this->connection->fetchFirstColumn(
            sprintf('SELECT %s FROM %s WHERE %s', $field, $table, $where),
            $params
        );

        return match (count($ids)) {
            1 => reset($ids),
            default => throw new LibraryException(
                sprintf(
                    'Ожидалось, что по указанному условию будет найдена 1 запись таблицы %s, найдено %d.',
                    $table,
                    count($ids)
                )
            ),
        };
    }

    /**
     * Добавить генерируемый функцией запрос.
     *
     * @psalm-param Callable(Parameters):string $generator
     */
    protected function addGeneratedSql(callable $generator): void
    {
        $params = new Parameters();
        $sql = $generator($params);
        $this->addSql($sql, $params->list, $params->types);
    }

    /**
     * Добавить INSERT-запрос в указанную таблицу.
     *
     * @psalm-param array<string, mixed> $fields
     */
    protected function addInsertSql(string $table, array $fields): void
    {
        $this->addGeneratedSql(fn (Parameters $_) => "INSERT INTO $table SET {$_->upsert($fields)}");
    }

    /**
     * Добавить INSERT-запрос в указанную таблицу с указанием IGNORE.
     *
     * @psalm-param array<string, mixed> $fields
     */
    protected function addInsertIgnoreSql(string $table, array $fields): void
    {
        $this->addGeneratedSql(fn (Parameters $_) => "INSERT IGNORE INTO $table SET {$_->upsert($fields)}");
    }

    /**
     * Добавить UPDATE-запрос в указанную таблицу.
     *
     * @psalm-param array<string, mixed> $fields
     * @psalm-param Callable(Parameters):string|null $where Генератор WHERE-условий
     */
    protected function addUpdateSql(string $table, array $fields, ?callable $where): void
    {
        $this->addGeneratedSql(fn (Parameters $_) => "
            UPDATE $table SET {$_->upsert($fields)} {$_->if($where !== null, fn () => 'WHERE ' . $where($_))}
        ");
    }

    /**
     * Добавить таблицу с динамически-формируемым названием.
     */
    protected function addDynamicallyNamedTableSql(
        string $sqlOperation,
        string $nameTemplate,
        string $substitutionQuery,
        string $definition = ''
    ): void {
        $nameParts = preg_split('/(\?)/', $nameTemplate, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        if (count($nameParts) === 1) {
            throw new InvalidArgumentException('Для создания таблицы со статичным именем используйте addSql');
        }

        $queries = [
            'SET @SUBSTITUTION = 0',
            "SELECT @SUBSTITUTION := $substitutionQuery",
            "SET @CREATE_TABLE = CONCAT(
                '$sqlOperation ',"
                . implode(', ', array_map(
                    fn (string $namePart) => $namePart === '?'
                        ? '@SUBSTITUTION'
                        : var_export($namePart, true),
                    $nameParts
                ))
                . ",
                '$definition'
            )",
            'PREPARE createTable FROM @CREATE_TABLE',
            'EXECUTE createTable',
            'DEALLOCATE PREPARE createTable',
        ];

        $this->addSql(implode('; ' . PHP_EOL, $queries));
    }

    /**
     * Добавить запрос на создание таблицы с динамическим названием.
     */
    protected function addCreateIblockTableSql(string $nameTemplate, string $iblockCondition, string $definition): void
    {
        $this->addDynamicallyNamedTableSql(
            'CREATE TABLE',
            $nameTemplate,
            "ID FROM b_iblock WHERE $iblockCondition",
            $definition
        );
    }

    /**
     * Добавить запрос удаляющий строки таблицы при выполнении определённого условия.
     */
    protected function addDeleteWhereSql(string $table, string $where, array $parameters): void
    {
        $this->addSql("DELETE FROM $table WHERE $where", $parameters, $this->guessedTypes($parameters));
    }

    /**
     * Добавить запрос удаляющий таблицу.
     */
    protected function addDropTableSql(string $table): void
    {
        $this->addSql("DROP TABLE `$table`");
    }

    /**
     * Добавить запрос очищающий таблицу.
     */
    protected function addTruncateTableSql(string $table): void
    {
        $this->addSql("TRUNCATE TABLE `$table`");
    }

    /**
     * Установить значение опции.
     *
     * @psalm-param non-empty-string $moduleId
     * @psalm-param non-empty-string $option
     * @psalm-param non-empty-string|null $siteId Если не указан, то установка опции делается для всех сайтов.
     */
    protected function addOptionUpdateSql(
        string $moduleId,
        string $option,
        string|int|float $value,
        ?string $siteId = null
    ): void {
        $this->addGeneratedSql(fn (Parameters $_) => "
            UPDATE b_option
            SET VALUE = {$_($value)}
            WHERE MODULE_ID = {$_($moduleId)} and NAME = {$_($option)}
            {$_->if($siteId !== null, fn () => "and SITE_ID = {$_($siteId)}")}
        ");
    }

    private function guessedTypes(array $parameters): array
    {
        $types = [];
        foreach ($parameters as $name => $value) {
            if (is_array($value) === false) {
                continue;
            }

            if (count($value) === 0) {
                throw new InvalidArgumentException(sprintf('Параметр с именем "%s" передаёт пустой массив', $name));
            }

            $types[$name] = is_integer(reset($value)) ? ArrayParameterType::INTEGER : ArrayParameterType::STRING;
        }

        return $types;
    }
}
