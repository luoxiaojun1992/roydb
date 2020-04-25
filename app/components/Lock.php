<?php

namespace App\components;

use App\components\transaction\Txn;

class Lock
{
    //todo

    public static function txnLock(Txn $txn, $lockKey)
    {
        $result = \SwFwLess\facades\etcd\Lock::lock($lockKey, 0, true);
        if (!$result) {
            //todo 判断锁的持有者
        }

        if ($result) {
            $txn->addLockKeys([$lockKey]);
        }

        //todo add txn id to lock info
    }

    public static function txnUnLock($lockKey)
    {
        //todo

        $result = \SwFwLess\facades\etcd\Lock::unlock($lockKey);
        if ($result) {
            //todo remove lock key from txn
        }

        return $result;
    }
}
