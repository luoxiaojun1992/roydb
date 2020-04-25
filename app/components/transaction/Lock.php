<?php

namespace App\components\transaction;

class Lock
{
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

    /**
     * @param Txn $txn
     * @param $lockKey
     * @return bool
     * @throws \Throwable
     */
    public static function txnUnLock(Txn $txn, $lockKey)
    {
        $result = \SwFwLess\facades\etcd\Lock::unlock($lockKey);
        if ($result) {
            $txn->removeLockKeys([$lockKey]);
        }

        return $result;
    }
}
