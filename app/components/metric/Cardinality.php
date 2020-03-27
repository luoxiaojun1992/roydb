<?php

namespace App\components\metric;

use App\components\storage\AbstractStorage;
use SwFwLess\facades\RateLimit;
use SwFwLess\facades\RedisPool;

class Cardinality
{
    const INITIAL_INTERVAL = 3600;
    const INTERVAL_CACHE_KEY = 'metric:cardinality:interval';
    const RATE_LIMIT_METRIC = 'metric:cardinality';

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

    public function dequeue()
    {
        //todo
    }

    /**
     * @param $schema
     * @param $index
     * @throws \Throwable
     */
    public function updateValue($schema, $index)
    {
        $period = $this->getSampleInterval();
        if ($period <= 0) {
            return;
        }
        $pass = RateLimit::pass(self::RATE_LIMIT_METRIC, $period, 1);
        if (!$pass) {
            return;
        }

        //todo using queue

        return;
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
        $pass = RateLimit::pass(self::RATE_LIMIT_METRIC, $period, 1);
        if (!$pass) {
            return;
        }

        //todo lock meta with schema

        $schemaMeta = $this->storage->getSchemaMetaData($schema);
        if (!$schemaMeta) {
            //todo log outer
            throw new \Exception('Schema ' . $schema . ' not exists');
        }

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
