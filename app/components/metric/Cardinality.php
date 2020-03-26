<?php

namespace App\components\metric;

use App\components\storage\AbstractStorage;

class Cardinality
{
    /** @var AbstractStorage */
    protected $storage;

    public static function create(AbstractStorage $storage)
    {
        return new static($storage);
    }

    public function __construct(AbstractStorage $storage)
    {
        $this->storage = $storage;
    }

    public function updateValue($schema, $index)
    {
        return;

        //todo using queue
        //todo rate limit

        $schemaMeta = $this->storage->getSchemaMetaData($schema);
        if (!$schemaMeta) {
            throw new \Exception('Schema ' . $schema . ' not exists');
        }

        //todo lock meta with schema

        $indexCardinality = $this->storage->estimateIndexCardinality($schema);

        $updatedSchemaMeta = $schemaMeta;

        $this->storage->setSchemaMetaData($schema, $schemaMeta);
    }
}
