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

    protected $schemaMeta;

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

        //TODO
        $this->schemaMeta = [
            'pk' => 'id',
            'columns' => [
                [
                    'name' => 'id',
                    'type' => 'int',
                    'length' => 11,
                    'default' => null,
                    'allow_null' => false,
                ],
                [
                    'name' => 'type',
                    'type' => 'int',
                    'length' => 11,
                    'default' => 0,
                    'allow_null' => false,
                ],
                [
                    'name' => 'name',
                    'type' => 'varchar',
                    'length' => 255,
                    'default' => '',
                    'allow_null' => false,
                ],
            ],
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
