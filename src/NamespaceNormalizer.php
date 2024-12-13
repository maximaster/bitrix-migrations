<?php

namespace Maximaster\BitrixMigrations;

use Doctrine\Migrations\Configuration\Configuration;

class NamespaceNormalizer
{
    public function normalize(string $namespace, Configuration $configuration): string
    {
        $dirs = $configuration->getMigrationDirectories();

        return array_key_exists($namespace, $dirs) ? $dirs[$namespace] : ($namespace === '' ? key($dirs) : $namespace);
    }
}
