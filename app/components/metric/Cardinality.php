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
        //todo using queue
        //todo rate limit
        return;
    }

    public function updateValueImmediately($schema, $index)
    {
        //todo rate limit

        $schemaMeta = $this->storage->getSchemaMetaData($schema);
        if (!$schemaMeta) {
            //todo log outer
            throw new \Exception('Schema ' . $schema . ' not exists');
        }

        //todo lock meta with schema

        $indexConfig = $schemaMeta['index'];

        foreach ($indexConfig as $i => $indexMeta) {
            if ($indexMeta['name'] === $index) {
                $indexConfig[$i]['cardinality'] = $this->storage->estimateIndexCardinality($schema, $index);
            }
        }

        $schemaMeta['index'] = $indexConfig;

        $this->storage->setSchemaMetaData($schema, $schemaMeta);
    }
}
