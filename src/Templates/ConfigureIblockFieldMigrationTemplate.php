<?php

declare(strict_types=1);

namespace Maximaster\BitrixMigrations\Templates;

use Doctrine\DBAL\Schema\Schema;
use Exception;
use Maximaster\Attributemplate\JustScalar\Param;
use Maximaster\Attributemplate\TemplateAttribute\Rename;
use Maximaster\Attributemplate\TemplateAttribute\SetValue;
use Maximaster\BitrixMigrations\BitrixMigration;
use Maximaster\BitrixMigrations\MigrationRef\MigrationRef;

#[Rename(new Param('MIGRATION_NAME'))]
final class ConfigureIblockFieldMigrationTemplate extends BitrixMigration
{
    #[SetValue(new Param('IBLOCK'))]
    private const MigrationRef IBLOCK = MigrationRef::TODO;

    #[SetValue(new Param('FIELD_ID'))]
    private const FIELD_ID = '';

    #[SetValue(new Param('IS_REQUIRED'))]
    private const IS_REQUIRED = 'N';

    // TODO указать значения.
    private const DEFAULT_VALUE = [];

    public function getDescription(): string
    {
        return 'Настроить поле "..." в инфоблоке "...".';
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function up(Schema $schema): void
    {
        $this->addInsertSql('b_iblock_fields', [
            'IBLOCK_ID' => self::IBLOCK->idSubquery(),
            'FIELD_ID' => self::FIELD_ID,
            'IS_REQUIRED' => self::IS_REQUIRED,
            'DEFAULT_VALUE' => serialize(self::DEFAULT_VALUE),
        ]);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function down(Schema $schema): void
    {
        $iblockId = self::IBLOCK->primaryId($this);
        $this->addDeleteWhereSql('b_iblock_fields', 'IBLOCK_ID = ? AND FIELD_ID = ?', [$iblockId, self::FIELD_ID]);
    }
}
