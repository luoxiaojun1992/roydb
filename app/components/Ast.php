<?php

namespace App\components;

use App\components\consts\Stmt;

class Ast
{
    private $sql;

    private $stmt;

    private $stmtType;

    public function __construct($sql, $stmt)
    {
        $this->sql = $sql;
        $this->stmt = $stmt;
        $this->parseStmtType();
    }

    protected function parseStmtType()
    {
        if (array_key_exists('SELECT', $this->stmt)) {
            $this->stmtType = Stmt::TYPE_SELECT;
        } elseif (array_key_exists('INSERT', $this->stmt)) {
            $this->stmtType = Stmt::TYPE_INSERT;
        } elseif (array_key_exists('DELETE', $this->stmt)) {
            $this->stmtType = Stmt::TYPE_DELETE;
        } elseif (array_key_exists('UPDATE', $this->stmt)) {
            $this->stmtType = Stmt::TYPE_UPDATE;
        }
    }

    /**
     * @return mixed
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * @return mixed
     */
    public function getStmt()
    {
        return $this->stmt;
    }

    /**
     * @param $stmt
     * @return $this
     */
    public function setStmt($stmt)
    {
        $this->stmt = $stmt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStmtType()
    {
        return $this->stmtType;
    }
}
