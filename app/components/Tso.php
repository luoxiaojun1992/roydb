<?php

namespace App\components;

use SwFwLess\facades\RedisPool;

class Tso
{
    /**
     * @throws \Throwable
     */
    public static function txnTs()
    {
        $redis = RedisPool::pick();
        try {
            return $redis->incr('seq:txn:ts')
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            RedisPool::release($redis);
        }
    }

    /**
     * @return int
     * @throws \Throwable
     */
    public static function txnCommitTs()
    {
        $redis = RedisPool::pick();
        try {
            return $redis->incr('seq:txn:commit:ts')
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            RedisPool::release($redis);
        }
    }
}
