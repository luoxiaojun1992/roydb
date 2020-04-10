<?php

namespace App\components\plans;

use App\components\Ast;
use App\components\storage\AbstractStorage;
use App\components\transaction\Snapshot;
use App\components\transaction\Txn;
use App\components\Tso;

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

    /**
     * @return int
     * @throws \Throwable
     */
    protected function getTxnTs()
    {
        return Tso::txnTs();
    }

    /**
     * @return int
     * @throws \Throwable
     */
    public function execute()
    {
        //todo lock snapshot

        $txnTs = $this->getTxnTs();

        $txnSnapShot = $this->storage->getTxnSnapShot();
        if (is_null($txnSnapShot)) {
            $txnSnapShot = new Snapshot();
        }
        $txnSnapShot->addIdList([$txnTs]);

        $txn = Txn::create($this->storage)
            ->setTs($txnTs)
            ->setTxnSnapshot($txnSnapShot);

        if ($txn->begin()) {
            if ($this->storage->saveTxnSnapShot($txnSnapShot)) {
                return $txnTs;
            } else {
                $txn->rollback();
            }
        }

        return 0;
    }
}
