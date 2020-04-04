<?php

namespace App\components\transaction;

use App\components\consts\Txn as TxnConst;
use App\components\transaction\log\RedoLog;
use App\components\transaction\log\UndoLog;

class Txn
{
    protected int $status = TxnConst::STATUS_PENDING;

    /** @var RedoLog[] $redoLogs  */
    protected array $redoLogs = [];

    /** @var UndoLog[] $undoLogs  */
    protected array $undoLogs = [];

    protected int $ts;

    protected array $lockKeys = [];

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return RedoLog[]
     */
    public function getRedoLogs(): array
    {
        return $this->redoLogs;
    }

    /**
     * @param RedoLog[] $redoLogs
     * @return $this
     */
    public function setRedoLogs(array $redoLogs): self
    {
        $this->redoLogs = $redoLogs;
        return $this;
    }

    /**
     * @param RedoLog[] $redoLogs
     * @return $this
     */
    public function addRedoLogs(array $redoLogs): self
    {
        $this->redoLogs = array_merge($this->redoLogs, $redoLogs);
        return $this;
    }

    /**
     * @return UndoLog[]
     */
    public function getUndoLogs(): array
    {
        return $this->undoLogs;
    }

    /**
     * @param UndoLog[] $undoLogs
     * @return $this
     */
    public function setUndoLogs(array $undoLogs): self
    {
        $this->undoLogs = $undoLogs;
        return $this;
    }

    /**
     * @param UndoLog[] $undoLogs
     * @return $this
     */
    public function addUndoLogs(array $undoLogs): self
    {
        $this->undoLogs = array_merge($this->undoLogs, $undoLogs);
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
    public function getLockKeys(): array
    {
        return $this->lockKeys;
    }

    /**
     * @param array $lockKeys
     * @return $this
     */
    public function setLockKeys(array $lockKeys): self
    {
        $this->lockKeys = $lockKeys;
        return $this;
    }

    /**
     * @param array $lockKeys
     * @return $this
     */
    public function addLockKeys(array $lockKeys): self
    {
        $this->lockKeys = array_merge($this->lockKeys, $lockKeys);
        return $this;
    }

    /**
     * @return false|string
     */
    public function __toString()
    {
        return json_encode([
            'status' => $this->getStatus(),
            'redo_logs' => array_map(fn(RedoLog $val) => $val->toArray(), $this->getRedoLogs()),
            'undo_logs' => array_map(fn(UndoLog $val) => $val->toArray(), $this->getUndoLogs()),
            'ts' => $this->getTs(),
            'lock_keys' => $this->getLockKeys(),
        ]);
    }
}
