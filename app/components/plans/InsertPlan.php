<?php

namespace App\components\plans;

use App\components\Ast;
use App\components\elements\Column;
use App\components\storage\AbstractStorage;
use App\components\utils\datatype\Type;
use PHPSQLParser\utils\ExpressionType;

class InsertPlan implements PlanInterface
{
    /** @var Ast */
    protected $ast;

    /** @var AbstractStorage */
    protected $storage;

    /** @var Column[] */
    protected $columns = [];

    protected $schema;
    protected $table;
    protected $schemaMeta;

    protected $values;
    protected $rows;

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

        $this->extractSchema();
        $this->extractValues();
        //todo sql校验
    }

    /**
     * @throws \Exception
     */
    protected function extractSchema()
    {
        $stmt = $this->ast->getStmt();
        if (!isset($stmt['INSERT'])) {
            throw new \Exception('Missing schema in the sql');
        }

        $this->schema = $stmt['INSERT'];

        $table = $this->schema[1]['table'];

        $schemaMetaData = $this->storage->getSchemaMetaData($table);
        if (is_null($schemaMetaData)) {
            throw new \Exception('Table ' . $table . ' not existed');
        }

        $this->schemaMeta = $schemaMetaData;
        $this->table = $table;
    }

    /**
     * @throws \Exception
     */
    protected function extractValues()
    {
        $stmt = $this->ast->getStmt();
        if (!isset($stmt['VALUES'])) {
            throw new \Exception('Missing values in the sql');
        }

        $this->values = $stmt['VALUES'];

        $rows = [];

        $schemaMetaData = $this->schemaMeta;

        $columns = $schemaMetaData['columns'];

        foreach ($this->values as $value) {
            $row = [];
            $data = $value['data'];
            foreach ($columns as $i => $column) {
                if (!$column['nullable']) {
                    if (!isset($data[$i])) {
                        throw new \Exception('Column ' . $column['name'] . 'can\'t be null');
                    }
                }

                $columnValObj = $data[$i];
                $columnVal = $this->extractColumnValueObj($columnValObj);

                if (!$column['nullable']) {
                    if (is_null($columnVal)) {
                        throw new \Exception('Column ' . $column['name'] . ' can\'t be null');
                    }
                }

                $columnVal = $this->extractColumnValue($column, $columnVal);

                $row[$column['name']] = $columnVal;
            }

            $rows[] = $row;
        }

        $this->rows = $rows;
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
     * @return array|mixed
     * @throws \Throwable
     */
    public function execute()
    {
        return $this->storage->add($this->table, $this->rows);

        //todo 主键、索引冲突、创建索引、schema校验
    }
}
