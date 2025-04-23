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
final class RenameIblockPropertyMigartionTemplate extends BitrixMigration
{
    #[SetValue(new Param('XML_ID'))]
    private const string XML_ID = '';

    #[SetValue(new Param('OLD_NAME'))]
    private const string OLD_NAME = '';

    #[SetValue(new Param('NEW_NAME'))]
    private const string NEW_NAME = '';

    public function getDescription(): string
    {
        return 'Переименовать свойство "..." ИБ ... в "...".';
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function up(Schema $schema): void
    {
        $this->renameProperty(self::NEW_NAME);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function down(Schema $schema): void
    {
        $this->renameProperty(self::OLD_NAME);
    }

    private function renameProperty(string $name): void
    {
        $this->addUpdateSql(
            'b_iblock_property',
            ['NAME' => $name],
            static fn (Parameters $_) => $_->allEqual(['XML_ID' => self::XML_ID])
        );
    }
}
