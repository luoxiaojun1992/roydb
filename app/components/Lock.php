<?php

namespace App\components;

use App\components\transaction\Txn;

class Lock
{
    //todo

    public static function txnLock(Txn $txn, $lockKey)
    {
        $txn->addLockKeys([$lockKey]);

        //todo add txn id to lock info
    }

    public static function txnUnLock($lockKey)
    {
        //todo

        return true;
    }
}
