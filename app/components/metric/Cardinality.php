<?php

namespace App\components\metric;

use App\components\storage\AbstractStorage;
use SwFwLess\facades\RateLimit;
use SwFwLess\facades\RedisPool;

class Cardinality
{
    const INITIAL_INTERVAL = 3600;
    const INTERVAL_CACHE_KEY = 'metric:cardinality:interval';
    const RATE_LIMIT_METRIC = 'rate_limit:metric:cardinality';
    const QUEUE_NAME = 'queue:metric:cardinality';

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

    /**
     * @throws \Throwable
     */
    public function dequeue()
    {
        $redis = RedisPool::pick('pika');

        while (true) {
            try {
                $newMetric = $redis->rPop(self::QUEUE_NAME);
                if (!$newMetric) {
                    sleep(1);
                    continue;
                }

                if ((!isset($newMetric['schema'])) || (!isset($newMetric['index']))) {
                    continue;
                }

                $this->updateValue($newMetric['schema'], $newMetric['index']);
            } catch (\Throwable $e) {
                RedisPool::release($redis);
                throw $e;
            }
        }
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

        $redis = RedisPool::pick('pika');

        try {
            $redis->lPush(self::QUEUE_NAME, json_encode(['schema' => $schema, 'index' => $index]));
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