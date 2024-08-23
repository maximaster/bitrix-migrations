<?php

declare(strict_types=1);

namespace Maximaster\BitrixMigrations;

use Bitrix\Main\Result;
use Doctrine\Migrations\Exception\AbortMigration;

/**
 * Базовый класс фикстур для Bitrix проекта.
 *
 * @SuppressWarnings(PHPMD.CamelCaseVariableName) why:intended
 * @SuppressWarnings(PHPMD.NumberOfChildren) why:intended
 */
abstract class BitrixFixtire extends BitrixMigration
{
    /**
     * Прекратить миграцию если $result имеет ошибки.
     *
     * @throws AbortMigration
     */
    protected function assertBitrixResult(Result $result): void
    {
        if ($result->isSuccess()) {
            return;
        }

        throw new AbortMigration(implode(PHP_EOL, $result->getErrorMessages()));
    }
}
