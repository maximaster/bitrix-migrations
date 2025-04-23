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
use Maximaster\BitrixMigrations\Sql\Parameters;

#[Rename(new Param('MIGRATION_NAME'))]
final class AddIblockMigrationTemplate extends BitrixMigration
{
    #[SetValue(new Guid())]
    private const XML_ID = '';

    #[SetValue(new Param('LID'))]
    private const LID = '';

    #[SetValue(new Param('IBLOCK_TYPE_ID'))]
    private const IBLOCK_TYPE_ID = '';

    #[SetValue(new Param('NAME'))]
    private const NAME = '';

    #[SetValue(new Param('CODE'))]
    private const CODE = '';

    #[SetValue(new Param('API_CODE'))]
    private const API_CODE = '';

    #[SetValue(new Param('LIST_PAGE_URL'))]
    private const LIST_PAGE_URL = '';

    #[SetValue(new Param('DETAIL_PAGE_URL'))]
    private const DETAIL_PAGE_URL = '';

    #[SetValue(new Param('SECTION_PAGE_URL'))]
    private const SECTION_PAGE_URL = '';

    public function getDescription(): string
    {
        // TODO заполнить описание.
        return 'Добавить инфоблок "...".';
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function up(Schema $schema): void
    {
        $this->addInsertSql('b_iblock', [
            'VERSION' => '1',
            'TIMESTAMP_X' => static fn () => 'NOW()',
            'IBLOCK_TYPE_ID' => self::IBLOCK_TYPE_ID,
            'LID' => self::LID,
            'CODE' => self::CODE,
            'API_CODE' => self::API_CODE,
            'NAME' => self::NAME,
            'XML_ID' => self::XML_ID,
            'ACTIVE' => 'Y',
            'LIST_PAGE_URL' => self::LIST_PAGE_URL,
            'DETAIL_PAGE_URL' => self::DETAIL_PAGE_URL,
            'SECTION_PAGE_URL' => self::SECTION_PAGE_URL,
            'DESCRIPTION_TYPE' => 'text',
            'RSS_ACTIVE' => 'N',
            'INDEX_ELEMENT' => 'Y',
            'INDEX_SECTION' => 'Y',
            'WORKFLOW' => 'Y',
            'BIZPROC' => 'N',
            'SECTION_CHOOSER' => 'P',
            'RIGHTS_MODE' => 'S',
            'PROPERTY_INDEX' => 'Y',
        ]);

        $iblockQuery = static fn (Parameters $_) => "(SELECT ID FROM b_iblock WHERE XML_ID = {$_(self::XML_ID)})";

        $this->addInsertSql('b_iblock_group', ['IBLOCK_ID' => $iblockQuery, 'GROUP_ID' => 1, 'PERMISSION' => 'X']);
        $this->addInsertSql('b_iblock_group', ['IBLOCK_ID' => $iblockQuery, 'GROUP_ID' => 2, 'PERMISSION' => 'R']);

        $this->addInsertSql('b_iblock_site', ['IBLOCK_ID' => $iblockQuery, 'SITE_ID' => self::LID]);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function down(Schema $schema): void
    {
        $iblockId = $this->idWhere('b_iblock', 'XML_ID = ?', [self::XML_ID]);

        $this->addDeleteWhereSql('b_iblock_site', 'IBLOCK_ID = ?', [$iblockId]);
        $this->addDeleteWhereSql('b_iblock_group', 'IBLOCK_ID = ?', [$iblockId]);
        $this->addDeleteWhereSql('b_iblock', 'ID = ?', [$iblockId]);
    }
}
