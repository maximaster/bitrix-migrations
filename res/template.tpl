<?php

/** @noinspection DuplicatedCode Миграции часто дублируют код, т.к. не должны иметь зависимостей */

declare(strict_types=1);

namespace <namespace>;

use Doctrine\DBAL\Schema\Schema;
use Exception;
use Maximaster\BitrixMigrations\BitrixMigration;

/**
 * См. getDescription.
 *
 * @migration
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Несущественно
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength) Может быть много строк кода с запросами, но в этом и суть миграции
 * @SuppressWarnings(PHPMD.CamelCaseVariableName) Для использования имени $_ в addGeneratedSql
 * @SuppressWarnings(PHPMD.ShortVariable) Для использования имени $_ в addGeneratedSql
 */
final class <className> extends BitrixMigration
{
    public function getDescription(): string
    {
        return null;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function up(Schema $schema): void
    {
<up>
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function down(Schema $schema): void
    {
<down>
    }
}
