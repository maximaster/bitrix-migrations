<?php

declare(strict_types=1);

namespace Maximaster\BitrixMigrations\Sql;

use ArrayAccess;
use Closure;
use Doctrine\DBAL\ArrayParameterType;
use InvalidArgumentException;
use ReturnTypeWillChange;

/**
 * Параметры связанные с запросом.
 */
class Parameters implements ArrayAccess
{
    public array $list = [];
    public array $types = [];

    private int $idx = 0;

    public function __invoke($value, ?string $name = null): string
    {
        if ($value instanceof Closure) {
            return $value($this);
        }

        $name = $name === null ? "p$this->idx" : $name;
        if (array_key_exists($name, $this->list)) {
            throw new InvalidArgumentException(sprintf('Параметр с именем "%s" уже существует', $name));
        }

        ++$this->idx;
        $this->list[$name] = $value;
        if (is_array($value)) {
            if (count($value) === 0) {
                throw new InvalidArgumentException(sprintf('Параметр с именем "%s" передаёт пустой массив', $name));
            }

            $this->types[$name] = is_integer(reset($value)) ? ArrayParameterType::INTEGER : ArrayParameterType::STRING;
        }

        return ":$name";
    }

    public function upsert(array $fields): string
    {
        return $this->joinFields($fields, '=', ',');
    }

    /**
     * @psalm-param non-empty-array<non-empty-string, scalar|callable|null> $fields
     */
    public function allEqual(array $fields): string
    {
        return $this->joinFields($fields, '=', ' AND ');
    }

    /**
     * @psalm-param non-empty-array<non-empty-string, non-empty-string> $fields
     */
    public function allLike(array $fields): string
    {
        return $this->joinFields($fields, 'LIKE', ' AND ');
    }

    private function joinFields(array $fields, string $operator, string $separator): string
    {
        $sqlFields = [];
        foreach ($fields as $field => $value) {
            $preparedValue = $value instanceof Closure ? $value($this) : $this($value);

            // Если расставлять скобочки в возвращаемом значении, то нет подсветки
            if (str_starts_with(ltrim($preparedValue), 'SELECT')) {
                $preparedValue = "($preparedValue)";
            }

            // Поле может иметь зарезервированное имя, его надо обернуть в `.
            // Но есть случаи когда к полю идёт обращение через таблицу, учтём.
            $fieldParts = array_map(static fn (string $fieldPart) => trim($fieldPart, '`'), explode('.', $field));
            $fieldQuery = '`' . implode('`.`', $fieldParts) . '`';

            $sqlFields[] = "$fieldQuery $operator $preparedValue";
        }

        return implode($separator, $sqlFields);
    }

    /**
     * Возвращает генерируемую строку, если $success равен true.
     *
     * @psalm-param Callable(Parameters):string $generator Генератор результирующей строки
     */
    public function if(bool $success, callable $generator): string
    {
        return $success ? $generator($this) : '';
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->list);
    }

    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->list[$offset] ?? null;
    }

    #[ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->list[$offset] = $value;
    }

    #[ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->list[$offset]);
    }
}
