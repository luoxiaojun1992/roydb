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

    protected $tableOptions;

    protected $columns;

    protected $pk;

    protected $columnsMeta = [];

    protected $schemaMeta = [];

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

        $this->tableOptions = $this->schema['options'];

        $this->columns = $this->schema['create-def']['sub_tree'];

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

        if (is_null($pk)) {
            throw new \Exception('Primary key is null');
        }

        $this->pk = $pk;

        //TODO
        $this->schemaMeta = [
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
            'partition' => [
                'key' => 'id',
                'range' => [
                    [
                        'lower' => '',
                        'upper' => 1000,
                    ]
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
