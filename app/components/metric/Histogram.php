<?php

namespace App\components\metric;

use App\components\storage\AbstractStorage;

class Histogram
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

    public function updateCount($schema, $index)
    {
        $schemaMeta = $this->storage->getSchemaMetaData($schema);
        if (!$schemaMeta) {
            throw new \Exception('Schema ' . $schema . ' not exists');
        }

        //todo lock meta with schema

        $updatedSchemaMeta = $schemaMeta;

        $this->storage->setSchemaMetaData($schema, $schemaMeta);
    }
}
