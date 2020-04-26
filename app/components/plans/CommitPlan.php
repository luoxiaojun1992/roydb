<?php

namespace App\components\plans;

use App\components\Ast;
use App\components\storage\AbstractStorage;
use App\components\transaction\Txn;

class CommitPlan
{
    /** @var Ast */
    protected $ast;

    /** @var AbstractStorage */
    protected $storage;

    protected $txnId;

    /**
     * DeletePlan constructor.
     * @param Ast $ast
     * @param AbstractStorage $storage
     * @throws \Exception
     */
    public function __construct(Ast $ast, AbstractStorage $storage, $txnId)
    {
        $this->ast = $ast;
        $this->storage = $storage;
        $this->txnId = $txnId;
    }

    /**
     * @return int
     * @throws \Throwable
     */
    public function execute()
    {
        //todo
        return Txn::fromTxnId($txnTs, $this->storage)->commit();
    }
}
