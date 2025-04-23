<?php

namespace {
    class CDBResult
    {
        /**
         * @return array<non-empty-string, scalar|null>|null The next row as an associative array, or null if there are no more rows.
         */
        public function Fetch(): array|null
        {
            return [];
        }
    }

    class CPerfomanceSQL
    {
        /**
         * @param array<array-key, non-empty-string> $arSelect
         * @param array<mixed> $arFilter
         * @param array<non-empty-string, 'ASC'|'DESC'|'asc'|'desc'>|list<non-empty-string> $arOrder
         * @param bool $bGroup
         * @param bool|array{nTopCount?: int<0, max>, SubstitutionFunction?: callable, bDescPageNumbering?: bool} $arNavStartParams
         */
        public static function GetList(
            $arSelect = [],
            $arFilter = [],
            $arOrder = [],
            $bGroup = false,
            $arNavStartParams = []
        ): CDBResult
        {
            return new CDBResult();
        }
    }
}

namespace Bitrix\Main {

    use Exception;

    class ArgumentException  extends Exception {}
    class SystemException  extends Exception {}
    class Loader {
        /**
         * @param non-empty-string $moduleId
         */
        public static function includeModule(string $moduleId): bool { return false; }
    }
}

namespace Bitrix\Main\ORM {
    class Entity
    {
        /**
         * @return list<non-empty-string>
         */
        public function compileDbTableStructureDump(): array
        {
            return ['-'];
        }
    }
}

namespace Bitrix\Main\ORM\Data {

    use Bitrix\Main\ORM\Entity;

    class DataManager
    {
        public static function getTableName(): string
        {
            return '';
        }

        public static function getEntity(): Entity
        {
            return new Entity();
        }
    }
}
