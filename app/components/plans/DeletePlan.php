<?php

namespace App\components\plans;

use App\components\Ast;
use App\components\optimizers\CostBasedOptimizer;
use App\components\optimizers\RulesBasedOptimizer;
use App\components\Parser;
use App\components\storage\AbstractStorage;
use PHPSQLParser\PHPSQLCreator;

class DeletePlan implements PlanInterface
{
    /** @var Ast */
    protected $ast;

    /** @var AbstractStorage */
    protected $storage;

    protected $schemas;

    /**
     * DeletePlan constructor.
     * @param Ast $ast
     * @param AbstractStorage $storage
     * @param int $txnId
     */
    public function __construct(Ast $ast, AbstractStorage $storage, int $txnId = 0)
    {
        $this->ast = $ast;
        $this->storage = $storage;
        $this->extractSchemas();
    }

    protected function extractSchemas()
    {
        $stmt = $this->ast->getStmt();
        if (isset($stmt['FROM'])) {
            $this->schemas = $stmt['FROM'];
        }
    }

    /**
     * @return array|mixed
     * @throws \PHPSQLParser\exceptions\UnsupportedFeatureException
     * @throws \Throwable
     */
    protected function query()
    {
        $stmt = $this->ast->getStmt();

        unset($stmt['DELETE']);

        $queryStmt = [];

        $columns = [];
        foreach ($this->schemas as $schema) {
            $table = $schema['table'];
            $schemaMeta = $this->storage->getSchemaMetaData($table);
            $columns[] = [
                'expr_type' => 'colref',
                'alias' => false,
                'base_expr' => $table . '.' . $schemaMeta['pk'],
                'sub_tree' => false,
                'delim' => false,
            ];
        }
        $queryStmt['SELECT'] = $columns;
        $queryStmt = array_merge($queryStmt, $stmt);

        $queryAst = Parser::fromSql(
            (new PHPSQLCreator())->create($queryStmt),
            $queryStmt
        )->parseAst();
        $plan = Plan::create($queryAst, $this->storage);
        $plan = RulesBasedOptimizer::fromPlan($plan)->optimize();
        $plan = CostBasedOptimizer::fromPlan($plan)->optimize();
        return $plan->execute();
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function execute()
    {
        $rows = $this->query();
        $deleted = 0;

        foreach ($this->schemas as $schema) {
            $table = $schema['table'];
            $schemaMeta = $this->storage->getSchemaMetaData($table);
            if (is_null($schemaMeta)) {
                throw new \Exception('Schema ' . $table . ' not exists');
            }

            $pkList = array_column($rows, $table . '.' . $schemaMeta['pk']);
            $deleted += $this->storage->del($table, $pkList);
        }

        return $deleted;

    }
}
