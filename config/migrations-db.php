<?php

declare(strict_types=1);

use Maximaster\BitrixLoader\BitrixLoader;

$bitrixLoader = BitrixLoader::fromComposerConfigExtra(__DIR__ . '/composer.json');

$settings = require $bitrixLoader->documentRoot . '/bitrix/.settings.php';
$connection = $settings['connections']['value']['default'];

return [
    'dbname' => $connection['database'],
    'user' => $connection['login'],
    'password' => $connection['password'],
    'host' => $connection['host'],
    'driver' => 'pdo_mysql',
    'charset' => 'utf8',
];
