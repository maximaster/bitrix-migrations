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
final class AddIblockSectionMigrationTemplate extends BitrixMigration
{
    #[SetValue(new Guid())]
    private const XML_ID = '';

    #[SetValue(new Param('IBLOCK'))]
    private const MigrationRef IBLOCK = MigrationRef::TODO;

    #[SetValue(new Param('IBLOCK_SECTION'))]
    private const MigrationRef IBLOCK_SECTION = MigrationRef::TODO;

    #[SetValue(new Param('DEPTH_LEVEL'))]
    private const DEPTH_LEVEL = 1;

    #[SetValue(new Param('ACTIVE'))]
    private const ACTIVE = 'Y';

    #[SetValue(new Param('GLOBAL_ACTIVE'))]
    private const GLOBAL_ACTIVE = 'Y';

    #[SetValue(new Param('NAME'))]
    private const NAME = '';

    #[SetValue(new Param('DESCRIPTION'))]
    private const DESCRIPTION = '';

    #[SetValue(new Param('DESCRIPTION_TYPE'))]
    private const DESCRIPTION_TYPE = 'text';

    #[SetValue(new Param('CODE'))]
    private const CODE = '';

    #[SetValue(new Param('SORT'))]
    private const SORT = 500;

    #[SetValue(new Param('SEARCHABLE_CONTENT'))]
    private const SEARCHABLE_CONTENT = '';

    public function getDescription(): string
    {
        return 'Добавить раздел "..." в инфоблок "...".';
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function up(Schema $schema): void
    {
        $this->addInsertSql('b_iblock_section', [
            'XML_ID' => self::XML_ID,
            'IBLOCK_ID' => self::IBLOCK->idSubquery(),
            'IBLOCK_SECTION_ID' => self::IBLOCK_SECTION->idSubquery(),
            'DEPTH_LEVEL' => self::DEPTH_LEVEL,
            'TIMESTAMP_X' => static fn () => 'NOW()',
            'DATE_CREATE' => static fn () => 'NOW()',
            'CREATED_BY' => 1,
            'ACTIVE' => self::ACTIVE,
            'GLOBAL_ACTIVE' => self::GLOBAL_ACTIVE,
            'NAME' => self::NAME,
            'DESCRIPTION' => self::DESCRIPTION,
            'DESCRIPTION_TYPE' => self::DESCRIPTION_TYPE,
            'CODE' => self::CODE,
            'SORT' => self::SORT,
            'SEARCHABLE_CONTENT' => self::SEARCHABLE_CONTENT,
        ]);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function down(Schema $schema): void
    {
        $this->addDeleteWhereSql('b_iblock_section', 'XML_ID = ?', [self::XML_ID]);
    }
}
