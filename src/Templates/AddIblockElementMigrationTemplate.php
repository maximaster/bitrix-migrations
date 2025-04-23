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

#[Rename(new Param('MIGRATION_NAME'))]
final class AddIblockElementMigrationTemplate extends BitrixMigration
{
    #[SetValue(new Guid())]
    private const XML_ID = '';

    #[SetValue(new Param('IBLOCK'))]
    private const MigrationRef IBLOCK = MigrationRef::TODO;

    #[SetValue(new Param('IBLOCK_SECTION'))]
    private const MigrationRef IBLOCK_SECTION = MigrationRef::TODO;

    #[SetValue(new Param('ACTIVE'))]
    private const ACTIVE = 'Y';

    #[SetValue(new Param('NAME'))]
    private const NAME = '';

    #[SetValue(new Param('CODE'))]
    private const CODE = '';

    #[SetValue(new Param('PREVIEW_TEXT'))]
    private const PREVIEW_TEXT = '';

    #[SetValue(new Param('DETAIL_TEXT'))]
    private const DETAIL_TEXT = '';

    public function getDescription(): string
    {
        return 'Добавить элемент "..." в инфоблок "...".';
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function up(Schema $schema): void
    {
        $this->addInsertIgnoreSql('b_iblock_element', [
            'XML_ID' => self::XML_ID,
            'IBLOCK_ID' => self::IBLOCK->idSubquery(),
            'IBLOCK_SECTION_ID' => self::IBLOCK_SECTION->idSubquery(),
            'ACTIVE' => self::ACTIVE,
            'NAME' => self::NAME,
            'CODE' => self::CODE,
            'PREVIEW_TEXT' => self::PREVIEW_TEXT,
            'DETAIL_TEXT' => self::DETAIL_TEXT,
        ]);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function down(Schema $schema): void
    {
        $id = $this->idWhere('b_iblock_element', 'XML_ID = ? ', [self::XML_ID]);

        $this->addDeleteWhereSql('b_iblock_element_property', 'IBLOCK_ELEMENT_ID = ?', [$id]);
        $this->addDeleteWhereSql('b_iblock_element', 'ID = ?', [$id]);
    }
}
