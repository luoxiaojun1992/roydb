<?php

namespace App\components\plans;

use App\components\Ast;
use App\components\elements\condition\Condition;
use App\components\elements\condition\ConditionTree;
use App\components\optimizers\CostBasedOptimizer;
use App\components\optimizers\RulesBasedOptimizer;
use App\components\Parser;
use App\components\storage\AbstractStorage;
use App\components\utils\datatype\Type;
use PHPSQLParser\PHPSQLCreator;
use PHPSQLParser\utils\ExpressionType;

class UpdatePlan implements PlanInterface
{
    /** @var Ast */
    protected $ast;

    /** @var AbstractStorage */
    protected $storage;

    protected $schemas;

    /** @var Condition|ConditionTree|null */
    protected $condition;

    protected $sets;
    protected $updateRow;

    protected $txnId;

    /**
     * UpdatePlan constructor.
     * @param Ast $ast
     * @param AbstractStorage $storage
     * @param int $txnId
     * @throws \Exception
     */
    public function __construct(Ast $ast, AbstractStorage $storage, int $txnId = 0)
    {
        $this->ast = $ast;
        $this->storage = $storage;
        $this->txnId = $txnId;

        $this->extractSchemas();
        $this->extractSets();
    }

    protected function extractSchemas()
    {
        $stmt = $this->ast->getStmt();
        if (isset($stmt['UPDATE'])) {
            $this->schemas = $stmt['UPDATE'];
        }
    }

    /**
     * @throws \Exception
     */
    protected function extractSets()
    {
        $stmt = $this->ast->getStmt();
        if (!isset($stmt['SET'])) {
            throw new \Exception('Missing values in the sql');
        }

        $this->sets = $stmt['SET'];

        $updateRow = [];

        foreach ($this->sets as $set) {
            $key = $set['sub_tree'][0]['base_expr'];
            $value = $set['sub_tree'][2];

            $updateRow[$key] = $value;
        }

        $this->updateRow = $updateRow;
    }

    /**
     * @return array|mixed
     * @throws \PHPSQLParser\exceptions\UnsupportedFeatureException
     * @throws \Throwable
     */
    protected function query()
    {
        $stmt = $this->ast->getStmt();

        unset($stmt['SET']);

        $queryStmt = [];

        $schemas = [];
        foreach ($this->schemas as $schema) {
            $table = $schema['table'];
            $schemaMeta = $this->storage->getSchemaMetaData($table);
            $schemas[] = [
                'expr_type' => ExpressionType::COLREF,
                'alias' => false,
                'base_expr' => $table . '.' . $schemaMeta['pk'],
                'sub_tree' => false,
                'delim' => false,
            ];
        }
        $queryStmt['SELECT'] = $schemas;
        $queryStmt['FROM'] = $stmt['UPDATE'];
        unset($stmt['UPDATE']);
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
     * @param $schema
     * @return array
     * @throws \Exception
     */
    protected function extractUpdateRowBySchema($schema)
    {
        $schemaMeta = $this->storage->getSchemaMetaData($schema);
        if (is_null($schemaMeta)) {
            throw new \Exception('Schema ' . $schema . ' not exists');
        }

        $columns = $schemaMeta['columns'];
        $pk = $schemaMeta['pk'];

        $updateRow = [];

        foreach ($columns as $i => $column) {
            if ($column === $pk) {
                continue;
            }

            $key = null;
            if (isset($this->updateRow[$schema . '.' . $column['name']])) {
                $key = $schema . '.' . $column['name'];
            }
            if (isset($this->updateRow[$column['name']])) {
                $key = $column['name'];
            }

            if (is_null($key)) {
                continue;
            }

            $columnValObj = $this->updateRow[$key];
            $columnVal = $this->extractColumnValueObj($columnValObj);

            if (!$column['nullable']) {
                if (is_null($columnVal)) {
                    throw new \Exception('Column ' . $column['name'] . ' can\'t be null');
                }
            }

            $columnVal = $this->extractColumnValue($column, $columnVal);

            $updateRow[$column['name']] = $columnVal;
        }

        return $updateRow;
    }

    protected function extractColumnValueObj($columnValObj)
    {
        $columnVal = null;
        if ($columnValObj['expr_type'] === ExpressionType::CONSTANT) {
            $columnVal = $columnValObj['base_expr'];
            $columnVal = Type::rawVal($columnVal);
        } else {
            //todo udf...
        }

        return $columnVal;
    }

    /**
     * @param $column
     * @param $columnVal
     * @return false|int|string
     * @throws \Exception
     */
    protected function extractColumnValue($column, $columnVal)
    {
        $columnValType = $column['type'];

        if ($columnValType === 'int') {
            if (!is_int($columnVal)) {
                if (!ctype_digit($columnVal)) {
                    throw new \Exception('Column ' . $column['name'] . ' must be integer');
                } else {
                    $columnVal = intval($columnVal);
                }
            }

            if ($columnVal > 0) {
                if ($columnVal >= pow(10, $column['length'])) {
                    throw new \Exception(
                        'Length of column ' . $column['name'] . ' can\'t be greater than ' .
                        (string)($column['length'])
                    );
                }
            } else {
                if ($columnVal <= (-1 * pow(10, $column['length'] - 1))) {
                    throw new \Exception(
                        'Length of column ' . $column['name'] . ' can\'t be less than ' .
                        (string)($column['length'])
                    );
                }
            }
        } elseif ($columnValType === 'varchar') {
            if (!is_string($columnVal)) {
                throw new \Exception('Column ' . $column['name'] . ' must be string');
            }
        }
        //todo more types

        return $columnVal;
    }

    /**
     * @return int
     * @throws \PHPSQLParser\exceptions\UnsupportedFeatureException
     * @throws \Throwable
     */
    public function execute()
    {
        $affectedRows = 0;

        $rows = $this->query();

        foreach ($this->schemas as $schema) {
            $table = $schema['table'];

            $schemaMeta = $this->storage->getSchemaMetaData($table);
            if (is_null($schemaMeta)) {
                throw new \Exception('Schema ' . $table . ' not exists');
            }

            $pkList = array_column($rows, $table . '.' . $schemaMeta['pk']);

            $affectedRows += $this->storage->update(
                $table,
                $pkList,
                $this->extractUpdateRowBySchema($table)
            );
        }

        return $affectedRows;
    }
}
