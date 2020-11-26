<?php

namespace App\components\transaction;

class Lock
{
    public static function txnLock(Txn $txn, $lockKey)
    {
        if (array_key_exists($lockKey, $txn->getLockKeys())) {
            return true;
        }

        $txn->addLockKeys([$lockKey]);

        $result = \SwFwLess\facades\etcd\Lock::lock($lockKey, 0, true);
        if (!$result) {
            $txn->removeLockKeys([$lockKey]);
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
