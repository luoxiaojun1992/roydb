<?php

namespace App\components\optimizers;

use App\components\elements\Column;
use App\components\elements\condition\Condition;
use App\components\elements\condition\ConditionTree;
use App\components\elements\Group;
use App\components\elements\Order;
use App\components\plans\Plan;
use App\components\plans\QueryPlan;

class RulesBasedOptimizer
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

    public function optimize()
    {
        if (!($this->plan->getExecutionPlan() instanceof QueryPlan)) {
            return $this->plan;
        }

        $this->setStorageGetLimit();
        $this->setCountAll();
        $this->setUsedColumns();

        return $this->plan;
    }

    protected function hasSubColumns($columns)
    {
        foreach ($columns as $column) {
            if ($column->hasSubColumns()) {
                return true;
            }
        }

        return false;
    }

    protected function setStorageGetLimit()
    {
        /** @var QueryPlan $queryPlan */
        $queryPlan = $this->plan->getExecutionPlan();
        $limit = $queryPlan->getLimit();
        if (is_null($limit)) {
            return;
        }
        if (!is_null($queryPlan->getOrders())) {
            return;
        }
        if (!is_null($queryPlan->getGroups())) {
            return;
        }
        $schemas = $queryPlan->getSchemas();
        if (!is_null($schemas)) {
            if (count($schemas) > 1) {
                return;
            }
        }

        //todo column agg udf 排除
        if ($this->hasSubColumns($queryPlan->getColumns())) {
            return;
        }

        $queryPlan->setStorageGetLimit($limit);
    }

    protected function setCountAll()
    {
        /** @var QueryPlan $queryPlan */
        $queryPlan = $this->plan->getExecutionPlan();
        if (!is_null($queryPlan->getGroups())) {
            return;
        }
        $schemas = $queryPlan->getSchemas();
        if (!is_null($schemas)) {
            if (count($schemas) > 1) {
                return;
            }
        } else {
            return;
        }
        if (!is_null($queryPlan->getCondition())) {
            return;
        }
        $columns = $queryPlan->getColumns();
        foreach ($columns as $column) {
            if (!$column->isUdf()) {
                if ($column->getType() !== 'const') {
                    return;
                }
            } else {
                if ($column->getValue() !== 'count') {
                    return;
                }
                $subColumns = $column->getSubColumns();
                foreach ($subColumns as $subColumn) {
                    if ($subColumn->hasSubColumns()) {
                        return;
                    }
                    if ($subColumn->getType() === 'colref') {
                        if ($subColumn->getValue() !== '*') {
                            return;
                        }
                    } else {
                        if ($subColumn->getType() !== 'const') {
                            return;
                        }
                    }
                }
            }
        }
        $queryPlan->setCountAll(true);
    }

    protected function setUsedColumns()
    {
        /** @var QueryPlan $queryPlan */
        $queryPlan = $this->plan->getExecutionPlan();

        $usedColumns = $this->getUsedColumns($queryPlan);

        $queryPlan->setUsedColumns($usedColumns);
    }

    /**
     * @param QueryPlan $plan
     * @return array
     */
    protected function getUsedColumns($plan)
    {
        return array_merge(
            $this->getUsedColumnsFromColumns($plan->getColumns()),
            $this->getUsedColumnsFromSchemas($plan->getSchemas()),
            $this->getUsedColumnsFromCondition($plan->getCondition()),
            $this->getUsedColumnsFromGroup($plan->getGroups()),
            $this->getUsedColumnsFromHaving($plan->getHaving()),
            $this->getUsedColumnsFromOrder($plan->getOrders())
        );
    }

    /**
     * @param Column[] $columns
     * @return array
     */
    protected function getUsedColumnsFromColumns($columns)
    {
        $usedColumns = [];

        foreach ($columns as $column) {
            if ($column->hasSubColumns()) {
                $usedColumns = array_merge($usedColumns, $this->getUsedColumnsFromColumns($column->getSubColumns()));
            } else {
                if ($column->getType() === 'colref') {
                    $usedColumns[] = $column->getValue();
                }
            }
        }

        return $usedColumns;
    }

    /**
     * @param $schemas
     * @return array
     */
    protected function getUsedColumnsFromSchemas($schemas)
    {
        $usedColumns = [];

        if (is_null($schemas)) {
            return $usedColumns;
        }

        /** @var QueryPlan $queryPlan */
        $queryPlan = $this->plan->getExecutionPlan();

        foreach ($schemas as $schema) {
            if (!isset($schema['ref_clause'])) {
                continue;
            }
            if ($schema['ref_clause'] === false) {
                continue;
            }

            $usedColumns = array_merge(
                $usedColumns,
                $this->getUsedColumnsFromCondition($queryPlan->extractConditions($schema['ref_clause']))
            );
        }

        return $usedColumns;
    }

    /**
     * @param Condition|ConditionTree $condition
     * @return array
     */
    protected function getUsedColumnsFromCondition($condition)
    {
        $usedColumns = [];

        if (is_null($condition)) {
            return $usedColumns;
        }

        if ($condition instanceof ConditionTree) {
            foreach ($condition->getSubConditions() as $subCondition) {
                $usedColumns = array_merge($usedColumns, $this->getUsedColumnsFromCondition($subCondition));
            }
        } else {
            $operands = $condition->getOperands();
            foreach ($operands as $operand) {
                if ($operand->getType() === 'colref') {
                    $usedColumns[] = $operand->getValue();
                }
            }
        }

        return $usedColumns;
    }

    /**
     * @param Group[] $groups
     * @return array
     */
    protected function getUsedColumnsFromGroup($groups)
    {
        $usedColumns = [];

        if (is_null($groups)) {
            return $usedColumns;
        }

        foreach ($groups as $group) {
            if ($group->getType() === 'colref') {
                $usedColumns[] = $group->getValue();
            }
        }

        return $usedColumns;
    }

    /**
     * @param $having
     * @return array
     */
    protected function getUsedColumnsFromHaving($having)
    {
        return $this->getUsedColumnsFromCondition($having);
    }

    /**
     * @param Order[] $orders
     * @return array
     */
    protected function getUsedColumnsFromOrder($orders)
    {
        $usedColumns = [];

        if (is_null($orders)) {
            return $usedColumns;
        }

        foreach ($orders as $order) {
            if ($order->getType() === 'colref') {
                $usedColumns[] = $order->getValue();
            }
        }

        return $usedColumns;
    }
}
