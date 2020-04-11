<?php

namespace App\components\transaction;

use App\components\consts\Log as LogConst;
use App\components\consts\Txn as TxnConst;
use App\components\storage\AbstractStorage;
use App\components\transaction\log\AbstractLog;
use App\components\transaction\log\RedoLog;
use App\components\transaction\log\UndoLog;
use App\components\Tso;

class Txn
{
    protected int $status = TxnConst::STATUS_PENDING;

    /** @var RedoLog[] $redoLogs  */
    protected array $redoLogs = [];

    /** @var UndoLog[] $undoLogs  */
    protected array $undoLogs = [];

    /** @var int */
    protected $ts;

    /** @var int */
    protected $commitTs;

    protected array $lockKeys = [];

    /** @var Snapshot */
    protected $txnSnapshot;

    /** @var AbstractStorage */
    protected $storage;

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
     * @return int
     */
    public function getCommitTs(): int
    {
        return $this->commitTs;
    }

    /**
     * @param int $commitTs
     * @return $this
     */
    public function setCommitTs(int $commitTs): self
    {
        $this->commitTs = $commitTs;
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
     * @return Snapshot
     */
    public function getTxnSnapshot(): Snapshot
    {
        return $this->txnSnapshot;
    }

    /**
     * @param Snapshot $txnSnapshot
     * @return $this
     */
    public function setTxnSnapshot(Snapshot $txnSnapshot): self
    {
        $this->txnSnapshot = $txnSnapshot;
        return $this;
    }

    /**
     * @return AbstractStorage
     */
    public function getStorage(): AbstractStorage
    {
        return $this->storage;
    }

    /**
     * @param AbstractStorage $storage
     * @return $this
     */
    public function setStorage(AbstractStorage $storage): self
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * @return false|string
     */
    public function __toString()
    {
        return json_encode([
            'status' => $this->getStatus(),
            'redo_logs' => array_map(fn(RedoLog $redoLog) => $redoLog->toArray(), $this->getRedoLogs()),
            'undo_logs' => array_map(fn(UndoLog $undoLog) => $undoLog->toArray(), $this->getUndoLogs()),
            'ts' => $this->getTs(),
            'lock_keys' => $this->getLockKeys(),
            'txn_snapshot' => $this->getTxnSnapshot()->toArray(),
        ]);
    }

    public function add()
    {
        return $this->getStorage()->addTxn($this->getTs(), (string)$this);
    }

    public function update()
    {
        return $this->getStorage()->updateTxn($this->getTs(), (string)$this);
    }

    protected function executeRedoLogs()
    {
        $this->executeLogs($this->getRedoLogs());
    }

    protected function executeUndoLogs()
    {
        $this->executeLogs($this->getUndoLogs());
    }

    /**
     * @param AbstractLog[] $logs
     */
    protected function executeLogs($logs)
    {
        foreach ($logs as $log) {
            switch ($log->getOp()) {
                case LogConst::OP_ADD_SCHEMA_DATA:
                    $this->storage->add($log->getSchema(), $log->getRows());
                    break;
                case LogConst::OP_UPDATE_SCHEMA_DATA:
                    $this->storage->update($log->getSchema(), $log->getRowPkList(), $log->getRows());
                    break;
                case LogConst::OP_DEL_SCHEMA_DATA:
                    $this->storage->del($log->getSchema(), $log->getRowPkList());
                    break;
                case LogConst::OP_ADD_SCHEMA_META:
                    $this->storage->addSchemaMetaData($log->getSchema(), $log->getMetaData());
                    break;
                case LogConst::OP_UPDATE_SCHEMA_META:
                    $this->storage->updateSchemaMetaData($log->getSchema(), $log->getMetaData());
                    break;
                case LogConst::OP_DEL_SCHEMA_META:
                    $this->storage->delSchemaMetaData($log->getSchema());
                    break;
            }
        }
    }

    /**
     * @return int
     * @throws \Throwable
     */
    public function begin()
    {
        if ($this->getStatus() !== TxnConst::STATUS_PENDING) {
            throw new \Exception('Txn[' . ((string)$this->getTs()) . '] status is not pending');
        }

        $txnTs = Tso::txnTs();
        $this->setTs($txnTs);

        //todo lock snapshot
        $txnSnapShot = $this->storage->getTxnSnapShot();
        if (is_null($txnSnapShot)) {
            $txnSnapShot = new Snapshot();
        }
        $txnSnapShot->addIdList([$txnTs]);

        $this->setStatus(TxnConst::STATUS_BEGIN);
        $result = $this->add();

        if ($result) {
            $result = $this->storage->saveTxnSnapShot($txnSnapShot);
            if (!$result) {
                $this->rollback();
            }
        }

        if ($result) {
            return $txnTs;
        } else {
            return 0;
        }
    }

    /**
     * @throws \Exception
     */
    public function rollback()
    {
        $txnStatus = $this->getStatus();

        if (!in_array($txnStatus, [TxnConst::STATUS_BEGIN, TxnConst::STATUS_ROLLBACK])) {
            throw new \Exception(
                'Txn[' . ((string)$this->getTs()) . '] status[' . ((string)$this->getStatus()) .
                '] not allowed to rollback'
            );
        }

        $this->executeUndoLogs();

        $continue = true;

        $txnSnapShot = $this->storage->getTxnSnapShot();
        if (!is_null($txnSnapShot)) {
            $txnTs = $this->getTs();
            if (in_array($txnTs, $txnSnapShot->getIdList())) {
                $txnSnapShot->delIdList([$txnTs]);
                if (!$this->storage->saveTxnSnapShot($txnSnapShot)) {
                    $continue = false;
                }
            }
        }

        if ($continue) {
            if ($txnStatus !== TxnConst::STATUS_ROLLBACK) {
                $this->setStatus(TxnConst::STATUS_ROLLBACK);
                if (!$this->update()) {
                    $continue = false;
                }
            }
        }

        if ($continue) {
            return $this->storage->delTxn($this->getTs());
        }

        return false;
    }

    /**
     * @throws \Throwable
     */
    public function commit()
    {
        if ($this->getStatus() !== TxnConst::STATUS_BEGIN) {
            throw new \Exception('Txn[' . ((string)$this->getTs()) . '] has not been begun');
        }

        $this->setCommitTs(Tso::txnCommitTs());
        $this->setStatus(TxnConst::STATUS_COMMITTED);
        $this->update();
    }

    /**
     * @return bool
     */
    public function valid()
    {
        $txnSnapShot = $this->storage->getTxnSnapShot();
        if (!is_null($txnSnapShot)) {
            $txnTs = $this->getTs();
            if (in_array($txnTs, $txnSnapShot->getIdList())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $txnId
     * @param AbstractStorage $storage
     * @return static
     * @throws \Exception
     */
    public static function fromTxnId($txnId, AbstractStorage $storage): self
    {
        $txnJson = $storage->getTxn($txnId);
        if (is_null($txnJson)) {
            throw new \Exception('Txn['. ((string)$txnId) .'] not exists');
        }

        $txnArr = json_decode($txnJson, true);

        return (new self())->setStatus($txnArr['status'])
            ->setRedoLogs(
                array_map(
                    fn($redoLogArr) => (new RedoLog())->setSchema($redoLogArr['schema'])
                        ->setRowPkList($redoLogArr['row_pk_list'])
                        ->setRows($redoLogArr['rows'])
                        ->setMetaData($redoLogArr['meta_data'])
                        ->setOp($redoLogArr['op'])
                        ->setTs($redoLogArr['ts']),
                    $txnArr['redo_logs']
                )
            )
            ->setUndoLogs(
                array_map(
                    fn($undoLogArr) => (new UndoLog())->setSchema($undoLogArr['schema'])
                        ->setRowPkList($undoLogArr['row_pk_list'])
                        ->setRows($undoLogArr['rows'])
                        ->setMetaData($undoLogArr['meta_data'])
                        ->setOp($undoLogArr['op'])
                        ->setTs($undoLogArr['ts']),
                    $txnArr['undo_logs']
                )
            )
            ->setTs($txnArr['ts'])
            ->setLockKeys($txnArr['lock_keys'])
            ->setTxnSnapshot((new Snapshot())->setIdList($txnArr['txn_snapshot']['id_list'])->setIdListGaps($txnArr['txn_snapshot']['id_list_gaps']))
            ->setStorage($storage);
    }

    /**
     * @param AbstractStorage $storage
     * @return static
     */
    public static function create(AbstractStorage $storage): self
    {
        return (new self())->setStorage($storage);
    }
}
