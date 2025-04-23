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
use Maximaster\BitrixMigrations\Sql\Parameters;
use ProjectMigration\Framework\Migration;

#[Rename(new Param('MIGRATION_NAME'))]
final class ConfigureIblockImageFieldMigrationTemplate extends BitrixMigration
{
    #[SetValue(new Param('IBLOCK'))]
    private const MigrationRef IBLOCK = MigrationRef::TODO;

    #[SetValue(new Param('FIELD_ID'))]
    private const FIELD_ID = '';

    #[SetValue(new Param('IS_REQUIRED'))]
    private const IS_REQUIRED = 'N';

    private const DEFAULT_VALUE = [
      'SCALE' => 'N',
      'WIDTH' => '',
      'HEIGHT' => '',
      'IGNORE_ERRORS' => 'N',
      'METHOD' => 'resample',
      'COMPRESSION' => 95,
      'USE_WATERMARK_TEXT' => 'N',
      'WATERMARK_TEXT' => '',
      'WATERMARK_TEXT_FONT' => '',
      'WATERMARK_TEXT_COLOR' => '',
      'WATERMARK_TEXT_SIZE' => '',
      'WATERMARK_TEXT_POSITION' => 'tl',
      'USE_WATERMARK_FILE' => 'N',
      'WATERMARK_FILE' => '',
      'WATERMARK_FILE_ALPHA' => '',
      'WATERMARK_FILE_POSITION' => 'tl',
      'WATERMARK_FILE_ORDER' => '',
    ];

    public function getDescription(): string
    {
        // TODO: заполнить описание.
        return 'Настроить поле "..." в инфоблоке "...".';
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function up(Schema $schema): void
    {
        $this->addInsertIgnoreSql('b_iblock_fields', [
            'IBLOCK_ID' => self::IBLOCK->idSubquery(),
            'FIELD_ID' => self::FIELD_ID,
            'IS_REQUIRED' => self::IS_REQUIRED,
            'DEFAULT_VALUE' => serialize(self::DEFAULT_VALUE),
        ]);

        $this->addUpdateSql('b_iblock_fields', [
            'IS_REQUIRED' => self::IS_REQUIRED,
            'DEFAULT_VALUE' => serialize(self::DEFAULT_VALUE),
        ], static fn (Parameters $_) => $_->allEqual([
            'IBLOCK_ID' => self::IBLOCK->idSubquery(),
            'FIELD_ID' => self::FIELD_ID,
        ]));
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function down(Schema $schema): void
    {
        $this->addDeleteWhereSql(
            'b_iblock_fields',
            'IBLOCK_ID = ? AND FIELD_ID = ?',
            [self::IBLOCK->primaryId($this), self::FIELD_ID]
        );
    }
}
