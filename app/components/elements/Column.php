<?php

namespace App\components\elements;

class Column
{
    const DATA_TYPE_VARCHAR = 'varchar';
    const DATA_TYPE_CHAR = 'char';
    const DATA_TYPE_INT = 'int';

    const DATA_TYPES_WITH_LENGTH = [
        self::DATA_TYPE_VARCHAR,
        self::DATA_TYPE_CHAR,
        self::DATA_TYPE_INT,
    ];

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

    /**
     * @param Column $column
     * @return bool
     */
    public function equals(self $column)
    {
        if ($column->getType() !== $this->getType()) {
            return false;
        }

        if ($column->getValue() !== $this->getValue()) {
            return false;
        }

        if ($column->getAlias() !== $this->getAlias()) {
            return false;
        }

        $subColumns = $this->getSubColumns();
        foreach ($column->getSubColumns() as $i => $subColumn) {
            if (!isset($subColumns[$i])) {
                return false;
            }

            if (!$subColumns[$i]->equals($subColumn)) {
                return false;
            }
        }

        return true;
    }
}
