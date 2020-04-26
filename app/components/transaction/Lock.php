<?php

namespace App\components\transaction;

use SwFwLess\facades\etcd\Etcd;

class Lock
{
    public static function txnLock(Txn $txn, $lockKey)
    {
        $result = \SwFwLess\facades\etcd\Lock::lock($lockKey, 0, true);
        if (!$result) {
            $txnTs = Etcd::get('lock_info:' . $lockKey);
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
