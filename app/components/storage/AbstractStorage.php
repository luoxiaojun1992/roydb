<?php

namespace App\components\storage;

abstract class AbstractStorage
{
    abstract public function get($schema, $condition, $limit, $indexSuggestions, $usedColumns);

    abstract public function getSchemaMetaData($schema);

    abstract public function setSchemaMetaData($schema, $metaData);

    abstract public function countAll($schema);

    abstract public function add($schema, $rows);

    abstract public function del($schema, $pkList);

    abstract public function update($schema, $pkList, $updateRow);

    abstract public function indexCardinality($schema);
}
