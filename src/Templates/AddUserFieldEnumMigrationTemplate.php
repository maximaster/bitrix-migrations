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
final class AddUserFieldEnumMigrationTemplate extends BitrixMigration
{
    #[SetValue(new Guid())]
    private const XML_ID = '';

    #[SetValue(new Param('USER_FIELD_XML_ID'))]
    private const USER_FIELD_XML_ID = '';

    #[SetValue(new Param('VALUE'))]
    private const VALUE = '';

    #[SetValue(new Param('DEF'))]
    private const DEF = 'N';

    #[SetValue(new Param('SORT'))]
    private const SORT = 500;

    public function getDescription(): string
    {
        return 'Создать опцию "..." пользовательского свойство "..." для ....';
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function up(Schema $schema): void
    {
        $this->addInsertSql('b_user_field_enum', [
            'XML_ID' => self::XML_ID,
            'USER_FIELD_ID' => static fn (Parameters $_) => "(SELECT ID FROM b_user_field WHERE XML_ID = {$_(self::USER_FIELD_XML_ID)})",
            'VALUE' => self::VALUE,
            'DEF' => self::DEF,
            'SORT' => self::SORT,
        ]);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function down(Schema $schema): void
    {
        $this->addDeleteWhereSql('b_user_field_enum', 'XML_ID = ?', [self::XML_ID]);
    }
}
