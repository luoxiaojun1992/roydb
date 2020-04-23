<?php

namespace App\services\roydb;

use App\components\optimizers\CostBasedOptimizer;
use App\components\optimizers\RulesBasedOptimizer;
use App\components\Parser;
use App\components\plans\Plan;
use App\components\storage\StorageBuilder;
use Roydb\DeleteResponse;
use Roydb\InsertResponse;
use Roydb\UpdateResponse;

/**
 */
class WriteService extends \SwFwLess\services\GrpcUnaryService implements WriteInterface
{

    /**
     * @param \Roydb\InsertRequest $request
     * @return InsertResponse
     * @throws \Throwable
     */
    public function Insert(\Roydb\InsertRequest $request)
    {
        $sql = $request->getSql();
        $ast = Parser::fromSql($sql)->parseAst();
        //todo 数据库权限检查
        $plan = Plan::create($ast, StorageBuilder::create());
        $plan = RulesBasedOptimizer::fromPlan($plan)->optimize();
        $plan = CostBasedOptimizer::fromPlan($plan)->optimize();
        $affectedRows = $plan->execute();

        return (new InsertResponse())->setAffectedRows($affectedRows);
    }

    /**
     * @param \Roydb\DeleteRequest $request
     * @return DeleteResponse
     * @throws \Throwable
     */
    public function Delete(\Roydb\DeleteRequest $request)
    {
        $sql = $request->getSql();
        $ast = Parser::fromSql($sql)->parseAst();
        //todo 数据库权限检查
        $plan = Plan::create($ast, StorageBuilder::create());
        $plan = RulesBasedOptimizer::fromPlan($plan)->optimize();
        $plan = CostBasedOptimizer::fromPlan($plan)->optimize();
        $affectedRows = $plan->execute();

        return (new DeleteResponse())->setAffectedRows($affectedRows);
    }

    /**
     * @param \Roydb\UpdateRequest $request
     * @return UpdateResponse
     * @throws \Throwable
     */
    public function Update(\Roydb\UpdateRequest $request)
    {
        $sql = $request->getSql();
        $ast = Parser::fromSql($sql)->parseAst();
        //todo 数据库权限检查
        $plan = Plan::create($ast, StorageBuilder::create());
        $plan = RulesBasedOptimizer::fromPlan($plan)->optimize();
        $plan = CostBasedOptimizer::fromPlan($plan)->optimize();
        $affectedRows = $plan->execute();

        return (new UpdateResponse())->setAffectedRows($affectedRows);
    }

}
