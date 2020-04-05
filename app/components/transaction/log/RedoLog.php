<?php

namespace App\components\transaction\log;

class RedoLog
{
    protected string $key;

    protected string $val;

    protected string $op;

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setKey(string $key): self
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getVal(): string
    {
        return $this->val;
    }

    /**
     * @param string $val
     * @return $this
     */
    public function setVal(string $val): self
    {
        $this->val = $val;
        return $this;
    }

    /**
     * @return string
     */
    public function getOp(): string
    {
        return $this->op;
    }

    /**
     * @param string $op
     * @return $this
     */
    public function setOp(string $op): self
    {
        $this->op = $op;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'key' => $this->getKey(),
            'val' => $this->getVal(),
            'op' => $this->getOp(),
        ];
    }
}
