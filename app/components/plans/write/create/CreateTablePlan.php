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

    public function execute()
    {
        var_dump($this->ast);
        return false;

        // TODO: Implement execute() method.

        return $this->storage->addSchemaMetaData($this->table, $this->schemaMeta);
    }
}
