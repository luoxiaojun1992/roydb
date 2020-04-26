<?php

namespace App\components\plans;

use App\components\Ast;
use App\components\storage\AbstractStorage;
use App\components\transaction\Txn;

class BeginPlan implements PlanInterface
{
    /** @var Ast */
    protected $ast;

    /** @var AbstractStorage */
    protected $storage;

    /**
     * BeginPlan constructor.
     * @param Ast $ast
     * @param AbstractStorage $storage
     * @param int $txnId
     */
    public function __construct(Ast $ast, AbstractStorage $storage, int $txnId = 0)
    {
        $this->ast = $ast;
        $this->storage = $storage;
    }

    /**
     * @return int
     * @throws \Throwable
     */
    public function execute()
    {
        return Txn::create($this->storage)->begin();
    }
}
