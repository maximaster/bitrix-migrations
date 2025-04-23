<?php

declare(strict_types=1);

namespace Maximaster\BitrixMigrations\Templates;

use Doctrine\DBAL\Schema\Schema;
use Exception;
use Maximaster\Attributemplate\JustScalar\Param;
use Maximaster\Attributemplate\TemplateAttribute\Rename;
use Maximaster\BitrixMigrations\BitrixMigration;
use Maximaster\BitrixMigrations\MigrationRef\MigrationRef;

#[Rename(new Param('MIGRATION_NAME'))]
final class RemoveUserFieldMigrationTemplate extends BitrixMigration
{
    private const string INSTALL_MIGRATION = self::class;
    private const string XML_ID = self::INSTALL_MIGRATION::XML_ID;
    private const string ENTITY_ID_PREFIX = self::INSTALL_MIGRATION::ENTITY_ID_PREFIX;
    private const MigrationRef ENTITY = self::INSTALL_MIGRATION::ENTITY;
    private const string ENTITY_ID_SUFFIX = self::INSTALL_MIGRATION::ENTITY_ID_SUFFIX;
    private const string FIELD_NAME = self::INSTALL_MIGRATION::FIELD_NAME;

    public function getDescription(): string
    {
        return 'Удалить пользовательское свойство "..." из "...".';
    }

    /**
     * @return array{non-empty-string, non-empty-string}
     */
    private function entityId(): array
    {
        /** @var non-empty-string $entityId */
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
        $this->addDeleteWhereSql(
            'b_user_field_lang',
            'USER_FIELD_ID = (SELECT ID FROM b_user_field WHERE XML_ID = ?)',
            [self::XML_ID]
        );

        $this->addDeleteWhereSql('b_user_field', 'XML_ID = ?', [self::XML_ID]);

        [, $entityCodeId] = $this->entityId();
        $this->addSql(sprintf('ALTER TABLE b_uts_%s DROP COLUMN %s', strtolower($entityCodeId), self::FIELD_NAME));
    }

    /**
     * {@inheritDoc}
     */
    public function down(Schema $schema): void
    {
        // Нецелесообразно, проще повторить запуск соответствующей миграции.
    }
}
