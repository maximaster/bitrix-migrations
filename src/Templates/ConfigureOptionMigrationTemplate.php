<?php

declare(strict_types=1);

namespace Maximaster\BitrixMigrations\Templates;

use Doctrine\DBAL\Schema\Schema;
use Exception;
use Maximaster\Attributemplate\JustScalar\Param;
use Maximaster\Attributemplate\TemplateAttribute\Rename;
use Maximaster\Attributemplate\TemplateAttribute\SetValue;
use Maximaster\BitrixMigrations\BitrixMigration;
use Maximaster\BitrixMigrations\Sql\Parameters;

#[Rename(new Param('MIGRATION_NAME'))]
final class ConfigureOptionMigrationTemplate extends BitrixMigration
{
    #[SetValue(new Param('MODULE_ID'))]
    private const string MODULE_ID = '';

    #[SetValue(new Param('NAME'))]
    private const string NAME = '';

    #[SetValue(new Param('VALUE'))]
    private const string VALUE = '';

    #[SetValue(new Param('OLD_VALUE'))]
    private const string OLD_VALUE = '';

    public function getDescription(): string
    {
        // TODO уточнить описание.
        return 'Настроить опцию "" для модуля "".';
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function up(Schema $schema): void
    {
        $this->configureValue(self::VALUE);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function down(Schema $schema): void
    {
        $this->configureValue(self::OLD_VALUE);
    }

    private function configureValue(mixed $value): void
    {
        $this->addInsertIgnoreSql(
            'b_option',
            ['MODULE_ID' => self::MODULE_ID, 'NAME' => self::NAME, 'VALUE' => $value]
        );

        $this->addUpdateSql(
            'b_option',
            ['VALUE' => $value],
            static fn (Parameters $_) => $_->allEqual(['MODULE_ID' => self::MODULE_ID, 'NAME' => self::NAME])
        );
    }
}
