<?php

namespace App\components\transaction;

class Lock
{
    public static function txnLock(Txn $txn, $lockKey)
    {
        //todo add txn id to lock info
        $result = \SwFwLess\facades\etcd\Lock::lock($lockKey, 0, true);
        if (!$result) {
            $txnTs = \SwFwLess\facades\etcd\Lock::get($lockKey);
            $result = intval($txnTs) === $txn->getTs();
        }

        if ($result) {
            $txn->addLockKeys([$lockKey]);
        }

        return $result;
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
