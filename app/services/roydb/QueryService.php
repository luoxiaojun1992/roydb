<?php

namespace App\services\roydb;

use App\components\optimizers\CostBasedOptimizer;
use App\components\optimizers\RulesBasedOptimizer;
use App\components\Parser;
use App\components\plans\Plan;
use App\components\storage\StorageBuilder;
use Roydb\Field;
use Roydb\RowData;
use Roydb\SelectResponse;

/**
 */
class QueryService extends \SwFwLess\services\GrpcUnaryService implements QueryInterface
{

    /**
     * @param \Roydb\SelectRequest $request
     * @return \Roydb\SelectResponse
     * @throws \Throwable
     */
    public function Select(\Roydb\SelectRequest $request)
    {
        $sql = $request->getSql();
        $ast = Parser::fromSql($sql)->parseAst();

        //todo 数据库权限检查
        $plan = Plan::create($ast, StorageBuilder::create());
        $plan = RulesBasedOptimizer::fromPlan($plan)->optimize();
        $plan = CostBasedOptimizer::fromPlan($plan)->optimize();
        $resultSet = $plan->execute();

        $rows = [];
        foreach ($resultSet as $row) {
            $rowData = new RowData();
            $fields = [];
            foreach ($row as $key => $value) {
                $field = new Field();
                $field->setKey($key)
                    ->setCharset('utf8');
                if (is_int($value)) {
                    $field->setValueType('integer')
                        ->setIntValue($value);
                } elseif (is_double($value) || is_float($value)) {
                    $field->setValueType('double')
                        ->setDoubleValue($value);
                } elseif (is_string($value)) {
                    $field->setValueType('string')
                        ->setStrValue($value);
                }
                $fields[] = $field;
            }
            $rowData->setField($fields);
            $rows[] = $rowData;
        }

        return (new SelectResponse())->setRowData($rows);
    }

}
