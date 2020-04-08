<?php

namespace App\components\transaction\log;

use phpDocumentor\Reflection\DocBlock\Tags\See;

class AbstractLog
{
    /** @var string|null */
    protected $schema;

    /** @var mixed[]|null */
    protected $rowPkList;

    /** @var array|null */
    protected $rows;

    /** @var string|null */
    protected $metaData;

    /** @var string|null */
    protected $op;

    /** @var int */
    protected $ts;

    /**
     * @return string|null
     */
    public function getSchema(): ?string
    {
        return $this->schema;
    }

    /**
     * @param string|null $schema
     * @return $this
     */
    public function setSchema(?string $schema): self
    {
        $this->schema = $schema;
        return $this;
    }

    /**
     * @return mixed[]|null
     */
    public function getRowPkList(): ?array
    {
        return $this->rowPkList;
    }

    /**
     * @param mixed[]|null $rowPkList
     * @return $this
     */
    public function setRowPkList(?array $rowPkList): self
    {
        $this->rowPkList = $rowPkList;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getRows(): ?array
    {
        return $this->rows;
    }

    /**
     * @param array|null $rows
     * @return $this
     */
    public function setRows(?array $rows): self
    {
        $this->rows = $rows;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMetaData(): ?string
    {
        return $this->metaData;
    }

    /**
     * @param string|null $metaData
     * @return $this
     */
    public function setMetaData(?string $metaData): self
    {
        $this->metaData = $metaData;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOp(): ?string
    {
        return $this->op;
    }

    /**
     * @param string|null $op
     * @return $this
     */
    public function setOp(?string $op): self
    {
        $this->op = $op;
        return $this;
    }

    /**
     * @return int
     */
    public function getTs(): int
    {
        return $this->ts;
    }

    /**
     * @param int $ts
     * @return $this
     */
    public function setTs(int $ts): self
    {
        $this->ts = $ts;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'schema' => $this->getSchema(),
            'row_pk_list' => $this->getRowPkList(),
            'rows' => $this->getRows(),
            'meta_data' => $this->getMetaData(),
            'op' => $this->getOp(),
            'ts' => $this->getTs(),
        ];
    }
}
