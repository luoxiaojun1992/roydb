<?php

namespace App\components\plans\write\create;

use App\components\Ast;
use App\components\elements\Column;
use App\components\plans\PlanInterface;
use App\components\storage\AbstractStorage;
use App\components\utils\datatype\Type;

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

    protected $indicies = [];

    protected $partitions = [];

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

//        $this->extractSchema();
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
            if ($column['expr_type'] === 'column-def') {
                $columnName = null;
                $isPk = false;
                $columnType = null;
                $columnLength = null;
                $columnUnsigned = null;
                $nullable = true;
                $defaultValue = null;

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
                                $columnLength = intval($columnTypeAttr['length']) ?? null;
                                $columnUnsigned = $columnTypeAttr['unsigned'] ?? null;
                            } elseif ($columnTypeAttr['expr_type'] === 'default-value') {
                                $defaultValue = Type::rawVal($columnTypeAttr['base_expr']);
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

                if (in_array($columnType, Column::DATA_TYPES_WITH_LENGTH)) {
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
                    'defaultValue' => $defaultValue,
                ];

                if (!is_null($columnLength)) {
                    $columnsMeta['length'] = $columnLength;
                }

                if (!is_null($columnUnsigned)) {
                    $columnsMeta['unsigned'] = $columnUnsigned;
                }

                $this->columnsMeta[] = $columnsMeta;
            } elseif ($column['expr_type'] === 'index') {
                $index = [];
                foreach ($column['sub_tree'] as $indexExpr) {
                    if ($indexExpr['expr_type'] === 'const') {
                        $index['name'] = $indexExpr['base_expr'];
                    } elseif ($indexExpr['expr_type'] === 'column-list') {
                        foreach ($indexExpr['sub_tree'] as $indexCol) {
                            //TODO length unique
                            if ($indexCol['expr_type'] === 'index-column') {
                                $index['columns'][] = [
                                    'name' => $indexCol['name'],
                                ];
                            }
                        }
                    }
                }
                $this->indicies[] = $index;
            }
        }

        $this->pk = $pk;

        $characterSet = null;
        $engine = null;
        $comment = null;

        foreach ($this->tableOptions as $tableOption) {
            if ($tableOption['expr_type'] === 'character-set') {
                foreach ($tableOption['sub_tree'] as $charSetExpr) {
                    if ($charSetExpr['expr_type'] === 'const') {
                        $characterSet = $charSetExpr['base_expr'];
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

        $partitionMeta = [];

        foreach ($this->partitionOptions as $partitionOption) {
            if ($partitionOption['expr_type'] === 'partition') {
                $partitionMeta['type'] = strtolower($partitionOption['by']);
            } elseif ($partitionOption['expr_type'] === 'partition-range') {
                foreach ($partitionOption['sub_tree'] as $rangeExpr) {
                    if ($rangeExpr['expr_type'] === 'bracket_expression') {
                        foreach ($rangeExpr['sub_tree'] as $column) {
                            $partitionMeta['column'] = $column['base_expr'];
                            break;
                        }
                    }
                }
            } elseif ($partitionOption['expr_type'] === 'bracket_expression') {
                $prevRangeBound = '';
                foreach ($partitionOption['sub_tree'] as $partition) {
                    if ($partition['expr_type'] === 'partition-def') {
                        $partitionPart = [];
                        foreach ($partition['sub_tree'] as $defExpr) {
                            if ($defExpr['expr_type'] === 'reserved') {
                                if ($defExpr['base_expr'] === 'partition') {
                                    $partitionPart['name'] = $defExpr['name'];
                                }
                            } elseif ($defExpr['expr_type'] === 'partition-values') {
                                foreach ($defExpr['sub_tree'] as $partitionValue) {
                                    foreach ($partitionValue as $pValExpr) {
                                        if ($pValExpr['expr_type'] === 'bracket_expression') {
                                            $partitionPart['lower'] = $prevRangeBound;
                                            $upper = $prevRangeBound = Type::rawVal($pValExpr['base_expr']);
                                            $partitionPart['upper'] = $upper;
                                        }
                                    }
                                }
                            }
                        }
                        $partitionMeta['parts'][] = $partitionPart;
                    }
                }
            }
        }

        $this->partitions[] = $partitionMeta;

        //TODO index(name、columns、unique)
        $this->schemaMeta = [
            'engine' => $engine,
            'comment' => $comment,
            'character_set' => $characterSet,
            'pk' => $this->pk,
            'columns' => $this->columnsMeta,
            'index' => $this->indicies,
            'partitions' => $this->partitions,
        ];
    }

    public function execute()
    {
        var_dump($this->ast);
        var_dump($this->schemaMeta);
        return false;

        // TODO: Implement execute() method.

        return $this->storage->addSchemaMetaData($this->table, $this->schemaMeta);
    }
}
