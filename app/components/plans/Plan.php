<?php

namespace App\components\plans;

use App\components\Ast;
use App\components\consts\Stmt;
use App\components\plans\write\create\CreateTablePlan;
use App\components\storage\AbstractStorage;

/**
 * Class Plan
 *
 * {@inheritDoc}
 *
 * In some situations, this class may composite several sub plans.
 *
 * @package App\components\plans
 */
class Plan
{
    const STMT_TYPE_PLAN_MAPPING = [
        Stmt::TYPE_SELECT => QueryPlan::class,
        Stmt::TYPE_INSERT => InsertPlan::class,
        Stmt::TYPE_DELETE => DeletePlan::class,
        Stmt::TYPE_UPDATE => UpdatePlan::class,
        Stmt::TYPE_BEGIN => BeginPlan::class,
        Stmt::TYPE_CREATE_TABLE => CreateTablePlan::class,
    ];

    /** @var Ast */
    protected $ast;

    /** @var AbstractStorage */
    protected $storage;

    /** @var QueryPlan|InsertPlan */
    protected $executePlan;

    protected $txnId;

    public static function create(Ast $ast, AbstractStorage $storage, int $txnId = 0)
    {
        return new static($ast, $storage, $txnId);
    }

    public function __construct(Ast $ast, AbstractStorage $storage, int $txnId = 0)
    {
        $this->ast = $ast;
        $this->storage = $storage;
        $this->txnId = $txnId;
        $this->generatePlan();
    }

    protected function generatePlan()
    {
        $planClass = self::STMT_TYPE_PLAN_MAPPING[$this->ast->getStmtType()];
        $this->executePlan = new $planClass($this->ast, $this->storage, $this->txnId);
    }

    /**
     * @return array|mixed
     * @throws \Throwable
     */
    public function execute()
    {
        return $this->executePlan->execute();
    }

    /**
     * @return mixed
     */
    public function getExecutePlan()
    {
        return $this->executePlan;
    }
}
