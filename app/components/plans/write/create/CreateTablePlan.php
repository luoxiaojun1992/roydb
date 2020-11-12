<?php

namespace App\components\plans\write\create;

use App\components\Ast;
use App\components\plans\PlanInterface;
use App\components\storage\AbstractStorage;

class CreateTablePlan implements PlanInterface
{
    /** @var Ast */
    protected $ast;

    /** @var AbstractStorage */
    protected $storage;

    protected $schema;

    protected $table;

    protected $tableOptions = [];

    protected $partitionOptions = [];

    protected $columns = [];

    protected $pk;

    protected $columnsMeta = [];

    protected $schemaMeta = [];

    protected $partitions = [];

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

    /**
     * @throws \Exception
     */
    protected function extractSchema()
    {
        $stmt = $this->ast->getStmt();
        if (!isset($stmt['CREATE'])) {
            throw new \Exception('Missing schema in the sql');
        }
        if (!isset($stmt['TABLE'])) {
            throw new \Exception('Missing schema in the sql');
        }

        $this->schema = $stmt['TABLE'];

        $table = $this->schema['name'];

        $schemaMetaData = $this->storage->getSchemaMetaData($table);
        if (!is_null($schemaMetaData)) {
            throw new \Exception('Table ' . $table . ' existed');
        }

        $this->table = $table;

        $this->tableOptions = $this->schema['options'] ?? [];

        $this->partitionOptions = $this->schema['partition-options'] ?? [];

        $this->columns = $this->schema['create-def']['sub_tree'] ?? [];

        $pk = null;

        foreach ($this->columns as $column) {
            $columnName = null;
            $isPk = false;
            $columnType = null;
            $columnLength = null;
            $columnUnsigned = null;
            $nullable = true;

            $attributes = $column['sub_tree'];
            foreach ($attributes as $attribute) {
                if ($attribute['expr_type'] === 'colref') {
                    $columnName = $attribute['base_expr'];
                } elseif ($attribute['expr_type'] === 'column-type') {
                    $isPk = $attribute['primary'];
                    $nullable = $attribute['nullable'];
                    $columnTypeAttrs = $attribute['sub_tree'];
                    foreach ($columnTypeAttrs as $columnTypeAttr) {
                        if ($columnTypeAttr['expr_type'] === 'data-type') {
                            $columnType = $columnTypeAttr['base_expr'];
                            $columnLength = $columnTypeAttr['length'] ?? null;
                            $columnUnsigned = $columnTypeAttr['unsigned'] ?? null;
                        }
                    }
                }
            }

            if (is_null($columnName)) {
                throw new \Exception('Missing column name');
            }

            if (!in_array($columnType, ['varchar', 'char', 'int', 'double', 'decimal'])) {
                throw new \Exception('Invalid column type');
            }

            if (in_array($columnType, ['varchar', 'char', 'int'])) {
                if (is_null($columnLength)) {
                    throw new \Exception('Missing column length');
                }
            }

            if (in_array($columnType, ['int', 'double', 'decimal'])) {
                if (is_null($columnUnsigned)) {
                    throw new \Exception('Missing column sign');
                }
            }

            if ($isPk) {
                $pk = $columnName;
            }

//            [
//                'name' => 'id',
//                'type' => 'int',
//                'length' => 11,
//                'default' => null,
//                'allow_null' => false,
//            ]
            $columnsMeta = [
                'name' => $columnName,
                'type' => $columnType,
                'nullable' => $nullable,
            ];

            if (!is_null($columnLength)) {
                $columnsMeta['length'] = $columnLength;
            }

            if (!is_null($columnUnsigned)) {
                $columnsMeta['unsigned'] = $columnUnsigned;
            }

            $this->columnsMeta[] = $columnsMeta;
        }

        $this->pk = $pk;

        $characterSet = null;
        $engine = null;
        $comment = null;

        foreach ($this->tableOptions as $tableOption) {
            if ($tableOption['expr_type'] === 'character-set') {
                foreach ($tableOption['sub_tree'] as $expr) {
                    if ($expr['expr_type'] === 'const') {
                        $characterSet = $expr['base_expr'];
                    }
                }
            } elseif ($tableOption['expr_type'] === 'expression') {
                $optionType = null;
                $optionValue = null;
                foreach ($tableOption['sub_tree'] as $expr) {
                    if ($expr['expr_type'] === 'reserved') {
                        if ($expr['base_expr'] === 'engine') {
                            $optionType = 'engine';
                        } elseif ($expr['base_expr'] === 'comment') {
                            $optionType = 'comment';
                        }
                    } elseif ($expr['expr_type'] === 'const') {
                        $optionValue = $expr['base_expr'];
                    }
                }
                if ($optionType === 'engine') {
                    $engine = $optionValue;
                } elseif ($optionType === 'comment') {
                    $comment = $optionValue;
                }
            }
        }

        $partition = [];

        foreach ($this->partitionOptions as $partitionOption) {
            //TODO
            if ($partitionOption['expr_type'] === 'partition') {
                $partition['type'] = strtolower($partitionOption['by']);
            } elseif ($partitionOption['expr_type'] === 'partition-range') {
                foreach ($partitionOption['sub_tree'] as $expr) {
                    if ($expr['expr_type'] === 'bracket_expression') {
                        foreach ($expr['sub_tree'] as $column) {
                            $partition['column'] = $column['base_expr'];
                            break;
                        }
                    }
                }
            } elseif ($partitionOption['expr_type'] === 'bracket_expression') {
                //TODO
            }
        }

        //TODO
        $this->schemaMeta = [
            'engine' => $engine,
            'comment' => $comment,
            'character_set' => $characterSet,
            'pk' => $this->pk,
            'columns' => $this->columnsMeta,
            'index' => [
                [
                    'name' => 'type',
                    'columns' => ['type'],
                    'unique' => false,
                ],
                [
                    'name' => 'name',
                    'columns' => ['name'],
                    'unique' => false,
                ],
            ],
            'partitions' => [
                [
                    'type' => 'range',
                    'column' => 'id',
                    'range' => [
                        [
                            'lower' => '',
                            'upper' => 1000,
                        ]
                    ],
                ],
            ],
        ];
    }

    public function execute()
    {
        var_dump($this->ast);
        return false;

        // TODO: Implement execute() method.

        return $this->storage->addSchemaMetaData($this->table, $this->schemaMeta);
    }
}
