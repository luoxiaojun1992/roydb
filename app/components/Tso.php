<?php

namespace App\components;

use SwFwLess\facades\etcd\Etcd;

class Tso
{
    /**
     * @throws \Throwable
     */
    public static function txnTs()
    {
        return Etcd::incr('seq:txn:ts');
    }

    /**
     * @return int
     * @throws \Throwable
     */
    public static function txnCommitTs()
    {
        return Etcd::incr('seq:txn:commit:ts');
    }
}
