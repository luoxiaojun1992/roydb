<?php

namespace App\components;

class Ast
{
    private $sql;

    private $stmt;

    private $stmtType;

    public function __construct($sql, $stmt)
    {
        $this->sql = $sql;
        $this->stmt = $stmt;
    }

    /**
     * @return mixed
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * @param $sql
     * @return $this
     */
    public function setSql($sql)
    {
        $this->sql = $sql;
        return $this;
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

    /**
     * @param $stmtType
     * @return $this
     */
    public function setStmtType($stmtType)
    {
        $this->stmtType = $stmtType;
        return $this;
    }
}
