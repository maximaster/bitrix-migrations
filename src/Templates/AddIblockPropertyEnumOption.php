<?php

declare(strict_types=1);

namespace Maximaster\BitrixMigrations\Templates;

use Doctrine\DBAL\Schema\Schema;
use Exception;
use Maximaster\Attributemplate\JustScalar\Guid;
use Maximaster\Attributemplate\JustScalar\Param;
use Maximaster\Attributemplate\TemplateAttribute\Rename;
use Maximaster\Attributemplate\TemplateAttribute\SetValue;
use Maximaster\BitrixMigrations\BitrixMigration;
use Maximaster\BitrixMigrations\MigrationRef\MigrationRef;
use Maximaster\BitrixMigrations\Sql\Parameters;

#[Rename(new Param('MIGRATION_NAME'))]
final class AddIblockPropertyEnumOption extends BitrixMigration
{
    #[SetValue(new Guid())]
    private const XML_ID = '';

    #[SetValue(new Param('IBLOCK'))]
    private const MigrationRef IBLOCK = MigrationRef::TODO;

    #[SetValue(new Param('PROPERTY_CODE'))]
    private const PROPERTY_CODE = '';

    #[SetValue(new Param('DEF'))]
    private const DEF = 'N';

    #[SetValue(new Param('VALUE'))]
    private const VALUE = '';

    #[SetValue(new Param('SORT'))]
    private const SORT = 500;

    public function getDescription(): string
    {
        // TODO уточнить описание.
        return 'Добавить вариант "..." в свойство "..." ИБ ....';
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function up(Schema $schema): void
    {
        $iblockId = self::IBLOCK->idSubquery();
        $propQuery = static fn (Parameters $_) => "(SELECT ID FROM b_iblock_property WHERE CODE = {$_(self::PROPERTY_CODE)} AND IBLOCK_ID = {$_($iblockId)})";

        $this->addInsertIgnoreSql(
            'b_iblock_property_enum',
            [
                'PROPERTY_ID' => $propQuery,
                'SORT' => self::SORT,
                'VALUE' => self::VALUE,
                'DEF' => self::DEF,
                'XML_ID' => self::XML_ID,
            ]
        );
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function down(Schema $schema): void
    {
        $iblockId = self::IBLOCK->primaryId($this);
        $propertyId = $this->idWhere(
            'b_iblock_property',
            'IBLOCK_ID = ? AND CODE = ?',
            [$iblockId, self::PROPERTY_CODE]
        );
        $this->addDeleteWhereSql(
            'b_iblock_property_enum',
            'PROPERTY_ID = ? AND XML_ID = ?',
            [$propertyId, self::XML_ID]
        );
    }
}
