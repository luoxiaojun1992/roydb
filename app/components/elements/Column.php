<?php

namespace App\components\elements;

class Column
{
    protected $type;

    protected $value;

    protected $alias;

    /** @var Column[]  */
    protected array $subColumns = [];

    public static function cloneFromColumn(self $column)
    {
        return (new self())
            ->settype($column->getType())
            ->setValue($column->getValue())
            ->setAlias($column->getAlias())
            ->setSubColumns($column->getSubColumns());
    }

    /**
     * @param $type
     * @return $this
     */
    public function setType($type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setValue($value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @param $alias
     * @return $this
     */
    public function setAlias($alias): self
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @param array $subColumns
     * @return $this
     */
    public function setSubColumns(array $subColumns): self
    {
        $this->subColumns = $subColumns;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    public function isUdf()
    {
        return in_array($this->getType(), ['aggregate_function', 'function']);
    }

    public function isColref()
    {
        return $this->getType() === 'colref';
    }

    public function isConst()
    {
        return $this->getType() === 'const';
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @return Column[]
     */
    public function getSubColumns(): array
    {
        return $this->subColumns;
    }

    /**
     * @return bool
     */
    public function hasSubColumns(): bool
    {
        return isset($this->subColumns[0]);
    }
}
