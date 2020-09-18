<?php

namespace App\components\plans;

use App\components\Ast;
use App\components\storage\AbstractStorage;

class CreatePlan implements PlanInterface
{
    /** @var Ast */
    protected $ast;

    /** @var AbstractStorage */
    protected $storage;

    /**
     * InsertPlan constructor.
     * @param Ast $ast
     * @param AbstractStorage $storage
     * @param int $txnId
     * @throws \Exception
     */
    public function __construct(Ast $ast, AbstractStorage $storage, int $txnId = 0)
    {
        $this->ast = $ast;
        $this->storage = $storage;

        //todo sql校验
    }


    public function execute()
    {
        // TODO: Implement execute() method.
    }
}
