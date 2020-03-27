<?php

namespace App\components\metric;

use App\components\storage\AbstractStorage;
use SwFwLess\facades\RateLimit;
use SwFwLess\facades\RedisPool;

class Cardinality
{
    const INITIAL_INTERVAL = 3600;
    const INTERVAL_CACHE_KEY = 'metric:cardinality:interval';

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

    /**
     * @return int
     * @throws \Throwable
     */
    protected function getSampleInterval()
    {
        $redis = RedisPool::pick('pika');
        try {
            $interval = intval($redis->get(self::INTERVAL_CACHE_KEY));
            if ($interval <= 0) {
                $interval = self::INITIAL_INTERVAL;
                $this->setSampleInterval($interval);
            }
            return $interval;
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            RedisPool::release($redis);
        }
    }

    /**
     * @param $interval
     * @throws \Throwable
     */
    protected function setSampleInterval($interval)
    {
        $redis = RedisPool::pick('pika');
        try {
            $redis->set(self::INTERVAL_CACHE_KEY, $interval);
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            RedisPool::release($redis);
        }
    }

    /**
     * @param $schema
     * @param $index
     * @throws \Throwable
     */
    public function updateValueImmediately($schema, $index)
    {
        $period = $this->getSampleInterval();
        if ($period <= 0) {
            return;
        }
        $pass = RateLimit::pass('metric:cardinality', $period, 1);
        if (!$pass) {
            return;
        }

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
