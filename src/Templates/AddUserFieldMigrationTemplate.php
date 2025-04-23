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
final class AddUserFieldMigrationTemplate extends BitrixMigration
{
    #[SetValue(new Guid())]
    private const XML_ID = '';

    #[SetValue(new Param('ENTITY_ID_PREFIX'))]
    private const ENTITY_ID_PREFIX = 'IBLOCK_';

    #[SetValue(new Param('ENTITY'))]
    private const MigrationRef ENTITY = MigrationRef::TODO;

    #[SetValue(new Param('ENTITY_ID_SUFFIX'))]
    private const ENTITY_ID_SUFFIX = '_SECTION';

    #[SetValue(new Param('USER_TYPE_ID'))]
    private const USER_TYPE_ID = 'string';

    #[SetValue(new Param('COLUMN_TYPE'))]
    private const COLUMN_TYPE = 'text';

    #[SetValue(new Param('FIELD_NAME'))]
    private const FIELD_NAME = 'UF_<?>';

    #[SetValue(new Param('SORT'))]
    private const SORT = 500;

    #[SetValue(new Param('MULTIPLE'))]
    private const MULTIPLE = 'N';

    #[SetValue(new Param('MANDATORY'))]
    private const MANDATORY = 'N';

    #[SetValue(new Param('HELP_MESSAGE'))]
    private const HELP_MESSAGE = '';

    #[SetValue(new Param('SHOW_FILTER'))]
    private const SHOW_FILTER = 'N';

    #[SetValue(new Param('SHOW_IN_LIST'))]
    private const SHOW_IN_LIST = 'Y';

    #[SetValue(new Param('EDIT_IN_LIST'))]
    private const EDIT_IN_LIST = 'Y';

    #[SetValue(new Param('IS_SEARCHABLE'))]
    private const IS_SEARCHABLE = 'N';

    #[SetValue(new Param('LANGUAGE_ID'))]
    private const LANGUAGE_ID = 'ru';

    #[SetValue(new Param('EDIT_FORM_LABEL'))]
    private const EDIT_FORM_LABEL = '';

    #[SetValue(new Param('LIST_COLUMN_LABEL'))]
    private const LIST_COLUMN_LABEL = '';

    #[SetValue(new Param('LIST_FILTER_LABEL'))]
    private const LIST_FILTER_LABEL = '';

    #[SetValue(new Param('ERROR_MESSAGE'))]
    private const ERROR_MESSAGE = '';

    #[SetValue(new Param('SETTINGS_SIZE'))]
    private const SETTINGS_SIZE = 100;

    #[SetValue(new Param('SETTINGS_ROWS'))]
    private const SETTINGS_ROWS = 1;

    #[SetValue(new Param('SETTINGS_REGEXP'))]
    private const SETTINGS_REGEXP = '';

    #[SetValue(new Param('SETTINGS_MIN_LENGTH'))]
    private const SETTINGS_MIN_LENGTH = 0;

    #[SetValue(new Param('SETTINGS_MAX_LENGTH'))]
    private const SETTINGS_MAX_LENGTH = 0;

    #[SetValue(new Param('SETTINGS_DEFAULT_VALUE'))]
    private const SETTINGS_DEFAULT_VALUE = '';

    public function getDescription(): string
    {
        // TODO заполнить описание.
        return 'Создать пользовательское свойство "..." для ....';
    }

    /**
     * @return array{non-empty-string, non-empty-string}
     */
    private function entityId(): array
    {
        $entityId = self::ENTITY->primaryId($this);
        $entityCodeId = self::ENTITY_ID_PREFIX . $entityId . self::ENTITY_ID_SUFFIX;

        return [$entityId, $entityCodeId];
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function up(Schema $schema): void
    {
        [$entityId, $entityCodeId] = $this->entityId();

        $this->addSql(
            sprintf('ALTER TABLE b_uts_%s ADD %s %s', strtolower($entityCodeId), self::FIELD_NAME, self::COLUMN_TYPE)
        );

        $this->addInsertSql('b_user_field', [
            'ENTITY_ID' => $entityCodeId,
            'FIELD_NAME' => self::FIELD_NAME,
            'USER_TYPE_ID' => self::USER_TYPE_ID,
            'XML_ID' => self::XML_ID,
            'SORT' => self::SORT,
            'MULTIPLE' => self::MULTIPLE,
            'MANDATORY' => self::MANDATORY,
            'SHOW_FILTER' => self::SHOW_FILTER,
            'SHOW_IN_LIST' => self::SHOW_IN_LIST,
            'EDIT_IN_LIST' => self::EDIT_IN_LIST,
            'IS_SEARCHABLE' => self::IS_SEARCHABLE,
            'SETTINGS' => serialize([
                'SIZE' => self::SETTINGS_SIZE,
                'ROWS' => self::SETTINGS_ROWS,
                'REGEXP' => self::SETTINGS_REGEXP,
                'MIN_LENGTH' => self::SETTINGS_MIN_LENGTH,
                'MAX_LENGTH' => self::SETTINGS_MAX_LENGTH,
                'DEFAULT_VALUE' => self::SETTINGS_DEFAULT_VALUE,
            ]),
        ]);

        $userFieldQuery = static fn (Parameters $_) => "(SELECT ID FROM b_user_field WHERE XML_ID = {$_(self::XML_ID)})";
        $this->addInsertSql('b_user_field_lang', [
            'USER_FIELD_ID' => $userFieldQuery,
            'LANGUAGE_ID' => self::LANGUAGE_ID,
            'EDIT_FORM_LABEL' => self::EDIT_FORM_LABEL,
            'LIST_COLUMN_LABEL' => self::LIST_COLUMN_LABEL,
            'LIST_FILTER_LABEL' => self::LIST_FILTER_LABEL,
            'ERROR_MESSAGE' => self::ERROR_MESSAGE,
            'HELP_MESSAGE' => self::HELP_MESSAGE,
        ]);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function down(Schema $schema): void
    {
        $this->addDeleteWhereSql(
            'b_user_field_lang',
            'USER_FIELD_ID = (SELECT ID FROM b_user_field WHERE XML_ID = ?)',
            [self::XML_ID]
        );

        $this->addDeleteWhereSql('b_user_field', 'XML_ID = ?', [self::XML_ID]);

        [, $entityCodeId] = $this->entityId();
        $this->addSql(sprintf('ALTER TABLE b_uts_%s DROP COLUMN %s', strtolower((string)$entityCodeId), self::FIELD_NAME));
    }
}
