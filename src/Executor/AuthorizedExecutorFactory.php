<?php

declare(strict_types=1);

namespace Maximaster\BitrixMigrations\Executor;

use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\InlineParameterFormatter;
use Doctrine\Migrations\Version\DbalExecutor;
use Doctrine\Migrations\Version\Executor;
use Maximaster\BitrixLoader\BitrixLoader;

/**
 * Возвращает исполнитель миграций, которые уже авторизован как админ в
 * Битриксе. Полезно для фикстур.
 */
class AuthorizedExecutorFactory
{
    private BitrixLoader $loader;

    public function __construct(BitrixLoader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseVariableName) why:dependency
     */
    public function __invoke(DependencyFactory $migratorFactory): Executor
    {
        $this->loader->prologBefore();

        global $USER;
        $USER->Authorize(1);

        return new DbalExecutor(
            $migratorFactory->getMetadataStorage(),
            $migratorFactory->getEventDispatcher(),
            $migratorFactory->getConnection(),
            $migratorFactory->getSchemaDiffProvider(),
            $migratorFactory->getLogger(),
            new InlineParameterFormatter($migratorFactory->getConnection()),
            $migratorFactory->getStopwatch()
        );
    }
}
