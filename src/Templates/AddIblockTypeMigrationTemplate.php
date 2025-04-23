<?php

declare(strict_types=1);

namespace Maximaster\BitrixMigrations\Templates;

use Doctrine\DBAL\Schema\Schema;
use Exception;
use Maximaster\Attributemplate\JustScalar\Param;
use Maximaster\Attributemplate\TemplateAttribute\Rename;
use Maximaster\Attributemplate\TemplateAttribute\SetValue;
use Maximaster\BitrixMigrations\BitrixMigration;

#[Rename(new Param('MIGRATION_NAME'))]
final class AddIblockTypeMigrationTemplate extends BitrixMigration
{
    #[SetValue(new Param('ID'))]
    private const ID = '';

    #[SetValue(new Param('NAME'))]
    private const NAME = '';

    #[SetValue(new Param('SECTIONS'))]
    private const SECTIONS = 'Y';

    #[SetValue(new Param('IN_RSS'))]
    private const IN_RSS = 'N';

    public function getDescription(): string
    {
        // TODO заполнить описание.
        return 'Добавить тип инфоблока "...".';
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function up(Schema $schema): void
    {
        $this->addInsertSql('b_iblock_type', ['ID' => self::ID, 'SECTIONS' => self::SECTIONS, 'IN_RSS' => self::IN_RSS]);
        $this->addInsertSql('b_iblock_type_lang', [
            'IBLOCK_TYPE_ID' => self::ID,
            'LID' => 'ru',
            'NAME' => self::NAME,
        ]);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function down(Schema $schema): void
    {
        $this->addDeleteWhereSql('b_iblock_type_lang', 'IBLOCK_TYPE_ID = ?', [self::ID]);
        $this->addDeleteWhereSql('b_iblock_type', 'ID = ?', [self::ID]);
    }
}
