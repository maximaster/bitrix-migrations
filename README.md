# maximaster/bitrix-migrations

Упрощает подключение на Битрикс-проект миграций через `doctrine/migrations` и
содежит собственный базовый класс для миграций с рядом полезных методов
под Битрикс.

Кроме этого, работа с миграциями ведётся так же как при базовом
использовании `doctrine/migrations`.

## Установка и первичная настройка

```php
composer require maximaster/bitrix-migrations
```

Далее установите стандартные конфиги требуемые `doctrine/migrations`:
```bash
./vendor/bin/bitrix-migrations init
```

## Использование

Основная польза библиотеки раскрывается, когда вы используете её специфичные
для Битрикса методы. Ярким примером служит метод `addCreateIblockTableSql`:

```php
$this->addCreateIblockTableSql('b_iblock_element_prop_s?', sprintf('XML_ID = "%s"', self::ID), '(
    IBLOCK_ELEMENT_ID INT(11) not null REFERENCES b_iblock_element(ID),
    PRIMARY KEY (IBLOCK_ELEMENT_ID)
)');
```

Здесь имя таблицы будет зависеть от ID инфоблока с нужным XML_ID.

Внутри используются [Prepared Statements](https://dev.mysql.com/doc/refman/8.0/en/sql-prepared-statements.html)
писать которые каждый раз было бы крайне громоздко.

Для эффективной работы с библиотекой рекомендуется сразу изучить защищённые
методы класса `BitrixMigration`.

Отдельно стоит отметить метод `addGeneratedSql`, который позволяет генерировать
запросы использующие подстановки данных "по месту", чтобы понять как данный
метод работает, посмотрите как он используется в других методах
`BitrixMigration`. Реализация была вдохновлена
[Thesis](https://phprussia.ru/moscow/2021/abstracts/7654), который на момент
обновления данного README, так и
[не был опубликован](https://github.com/thesisphp/thesis/issues/2#issuecomment-907701813).

## Обратная совместимость

Миграции должны зависеть от стороннего кода минимально, иначе, если этот
сторонний код изменяется, миграции которые ранее отрабатывали по одной логике,
после этих изменений могут начать работать по другому, либо вообще упасть.

Поэтому, данная библиотека строго придерживается
[семантическому версионированию](https://semver.org/lang/ru/) и если вы
зафиксировали мажорную версию, то никаких "сюрпризов" в миграциях после
обновлений данной зависимости происходить не должно.

## Дополнительные функции

Если пользуетесь `symfony/console`, то можете подключить в своё консольное
приложение доп. команды данного пакета (пока есть только одна):

* `bitrix-migrations:generate-table` - генерирует миграцию, которая создаёт
  таблицу в базе данных по DataManager-классу.

```php
$bitrixLoader = \Maximaster\BitrixLoader\BitrixLoader::fromComposerConfigExtra(__DIR__ . '/../composer.json');
$bitrixMigrationsCommandsFactory = require __DIR__ . '/../vendor/maximaster/bitrix-migrations/config/commands.php';
$app->addCommands($bitrixMigrationsCommandsFactory($bitrixLoader));
```
