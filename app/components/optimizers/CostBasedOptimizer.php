<?php

namespace App\components\optimizers;

use App\components\plans\Plan;
use App\components\plans\QueryPlan;

class CostBasedOptimizer
{
    /** @var Plan */
    protected $plan;

    public static function fromPlan(Plan $plan)
    {
        return new static($plan);
    }

    public function __construct(Plan $plan)
    {
        $this->plan = $plan;
    }

    /**
     * @return Plan
     */
    public function optimize()
    {
        if (!($this->plan->getExecutionPlan() instanceof QueryPlan)) {
            return $this->plan;
        }

        //todo 基于统计数据对condition的cost打分，在and条件下选择代价小的condition

        return $this->plan;
    }
}
