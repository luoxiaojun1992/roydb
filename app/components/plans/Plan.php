<?php

namespace App\components\plans;

use App\components\Ast;
use App\components\consts\Stmt;
use App\components\storage\AbstractStorage;

class Plan
{
    const STMT_TYPE_PLAN_MAPPING = [
        Stmt::TYPE_SELECT => QueryPlan::class,
        Stmt::TYPE_INSERT => InsertPlan::class,
        Stmt::TYPE_DELETE => DeletePlan::class,
        Stmt::TYPE_UPDATE => UpdatePlan::class,
        Stmt::TYPE_BEGIN => BeginPlan::class,
    ];

    /** @var Ast */
    protected $ast;

    /** @var AbstractStorage */
    protected $storage;

    /** @var QueryPlan|InsertPlan */
    protected $executePlan;

    public static function create(Ast $ast, AbstractStorage $storage)
    {
        return new static($ast, $storage);
    }

    public function __construct(Ast $ast, AbstractStorage $storage)
    {
        $this->ast = $ast;
        $this->storage = $storage;
        $this->generatePlan();
    }

    protected function generatePlan()
    {
        $planClass = self::STMT_TYPE_PLAN_MAPPING[$this->ast->getStmtType()];
        $this->executePlan = new $planClass($this->ast, $this->storage);
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
