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
final class AddIblockPropertyMigrationTemplate extends BitrixMigration
{
    #[SetValue(new Guid())]
    private const XML_ID = '...';

    #[SetValue(new Param('IBLOCK'))]
    private const MigrationRef IBLOCK = MigrationRef::TODO;

    #[SetValue(new Param('NAME'))]
    private const NAME = '';

    #[SetValue(new Param('CODE'))]
    private const CODE = '';

    #[SetValue(new Param('PROPERTY_TYPE'))]
    private const PROPERTY_TYPE = 'S';

    #[SetValue(new Param('USER_TYPE'))]
    private const USER_TYPE = null;

    #[SetValue(new Param('MULTIPLE'))]
    private const MULTIPLE = 'N';

    #[SetValue(new Param('WITH_DESCRIPTION'))]
    private const WITH_DESCRIPTION = 'N';

    #[SetValue(new Param('IS_REQUIRED'))]
    private const IS_REQUIRED = 'N';

    #[SetValue(new Param('ACTIVE'))]
    private const ACTIVE = 'Y';

    #[SetValue(new Param('SORT'))]
    private const SORT = '500';

    #[SetValue(new Param('DEFAULT_VALUE'))]
    private const DEFAULT_VALUE = '';

    #[SetValue(new Param('ROW_COUNT'))]
    private const ROW_COUNT = '1';

    #[SetValue(new Param('COL_COUNT'))]
    private const COL_COUNT = '30';

    #[SetValue(new Param('LIST_TYPE'))]
    private const LIST_TYPE = 'L';

    #[SetValue(new Param('FILE_TYPE'))]
    private const FILE_TYPE = '';

    #[SetValue(new Param('MULTIPLE_CNT'))]
    private const MULTIPLE_CNT = '5';

    #[SetValue(new Param('LINK_IBLOCK'))]
    private const MigrationRef LINK_IBLOCK = MigrationRef::TODO;

    #[SetValue(new Param('SEARCHABLE'))]
    private const SEARCHABLE = 'N';

    #[SetValue(new Param('FILTRABLE'))]
    private const FILTRABLE = 'N';

    #[SetValue(new Param('VERSION'))]
    private const VERSION = '1';

    #[SetValue(new Param('USER_TYPE_SETTINGS'))]
    private const USER_TYPE_SETTINGS = [];

    #[SetValue(new Param('HINT'))]
    private const HINT = '';

    public function getDescription(): string
    {
        // TODO: заполнить описание.
        return 'Создать свойство "..." в ИБ "...".';
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function up(Schema $schema): void
    {
        $this->addInsertSql('b_iblock_property', [
            'XML_ID' => self::XML_ID,
            'NAME' => self::NAME,
            'CODE' => self::CODE,
            'IBLOCK_ID' => self::IBLOCK->idSubquery(),
            'TIMESTAMP_X' => static fn () => 'NOW()',
            'PROPERTY_TYPE' => self::PROPERTY_TYPE,
            'MULTIPLE' => self::MULTIPLE,
            'WITH_DESCRIPTION' => self::WITH_DESCRIPTION,
            'IS_REQUIRED' => self::IS_REQUIRED,
            'ACTIVE' => self::ACTIVE,
            'SORT' => self::SORT,
            'DEFAULT_VALUE' => self::DEFAULT_VALUE,
            'ROW_COUNT' => self::ROW_COUNT,
            'COL_COUNT' => self::COL_COUNT,
            'LIST_TYPE' => self::LIST_TYPE,
            'FILE_TYPE' => self::FILE_TYPE,
            'MULTIPLE_CNT' => self::MULTIPLE_CNT,
            'LINK_IBLOCK_ID' => self::LINK_IBLOCK->idSubquery(),
            'SEARCHABLE' => self::SEARCHABLE,
            'FILTRABLE' => self::FILTRABLE,
            'VERSION' => self::VERSION,
            'USER_TYPE' => self::USER_TYPE,
            'USER_TYPE_SETTINGS' => serialize(self::USER_TYPE_SETTINGS),
            'HINT' => self::HINT,
        ]);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function down(Schema $schema): void
    {
        $this->addDeleteWhereSql('b_iblock_property', 'XML_ID = ?', [self::XML_ID]);
    }
}
