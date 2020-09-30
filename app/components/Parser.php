<?php

namespace App\components;

use App\components\consts\Stmt;
use PHPSQLParser\PHPSQLParser;

class Parser
{
    private $sql;

    private $simpleAst;

    public static function fromSql($sql, $simpleAst = null)
    {
        return (new static($sql, $simpleAst));
    }

    public function __construct($sql, $simpleAst = null)
    {
        $this->sql = $sql;
        $this->simpleAst = $simpleAst;
    }

    public function parseAst()
    {
        if (is_null($this->simpleAst)) {
            $this->simpleAst = (new PHPSQLParser())->parse($this->sql);
        }

        $ast = new Ast($this->sql, $this->simpleAst);
        return $this->parseStmtType($ast);
    }

    protected function parseStmtType(Ast $ast)
    {
        $stmtType = null;

        $sql = $ast->getSql();
        $stmt = $ast->getStmt();

        if ($stmt !== false) {
            if (array_key_exists('SELECT', $stmt)) {
                $stmtType = Stmt::TYPE_SELECT;
            } elseif (array_key_exists('INSERT', $stmt)) {
                $stmtType = Stmt::TYPE_INSERT;
            } elseif (array_key_exists('DELETE', $stmt)) {
                $stmtType = Stmt::TYPE_DELETE;
            } elseif (array_key_exists('UPDATE', $stmt)) {
                $stmtType = Stmt::TYPE_UPDATE;
            } elseif (array_key_exists('CREATE', $stmt)) {
                if (array_key_exists('TABLE', $stmt)) {
                    $stmtType = Stmt::TYPE_CREATE_TABLE;
                }
            }
        } else {
            if ($sql === 'BEGIN') {
                $stmtType = Stmt::TYPE_BEGIN;
            } elseif ($sql === 'COMMIT') {
                $stmtType = Stmt::TYPE_COMMIT;
            }
        }

        return $ast->setStmtType($stmtType);
    }
}
