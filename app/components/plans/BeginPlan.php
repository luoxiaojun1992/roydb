<?php

namespace App\components\plans;

use App\components\Ast;
use App\components\storage\AbstractStorage;
use App\components\transaction\Snapshot;
use App\components\transaction\Txn;

class BeginPlan
{
    /** @var Ast */
    protected $ast;

    /** @var AbstractStorage */
    protected $storage;

    /**
     * DeletePlan constructor.
     * @param Ast $ast
     * @param AbstractStorage $storage
     * @throws \Exception
     */
    public function __construct(Ast $ast, AbstractStorage $storage)
    {
        $this->ast = $ast;
        $this->storage = $storage;
    }

    protected function getTxnTs()
    {
        //todo sequence from redis

        return 1;
    }

    public function execute()
    {
        //todo

        $txnTs = $this->getTxnTs();

        /** @var Snapshot $txnSnapShot */
        $txnSnapShot = $this->storage->getTxnSnapShot();
        if (is_null($txnSnapShot)) {
            $txnSnapShot = new Snapshot();
        }
        $txnSnapShot->addIdList([$txnTs]);

        $txn = Txn::create($this->storage)
            ->setTs($txnTs)
            ->setTxnSnapshot($txnSnapShot);

        if ($txn->save()) {
            if ($this->storage->saveTxnSnapShot($txnSnapShot)) {
                return $txnTs;
            } else {
                $txn->rollback();
            }
        }

        return 0;
    }
}
