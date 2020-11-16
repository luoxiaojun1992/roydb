<?php

namespace App\components\transaction;

use App\components\consts\Log as LogConst;
use App\components\consts\Txn as TxnConst;
use App\components\storage\AbstractStorage;
use App\components\storage\StorageFactory;
use App\components\transaction\log\AbstractLog;
use App\components\transaction\log\RedoLog;
use App\components\transaction\log\UndoLog;
use App\components\Tso;
use SwFwLess\components\utils\Arr;

class Txn
{
    protected int $status = TxnConst::STATUS_PENDING;

    /** @var RedoLog[] $redoLogs  */
    protected array $redoLogs = [];

    /** @var UndoLog[] $undoLogs  */
    protected array $undoLogs = [];

    /** @var int $ts */
    protected $ts;

    /** @var int $commitTs */
    protected $commitTs;

    /** @var array $lockKeys */
    protected array $lockKeys = [];

    /** @var Snapshot $txnSnapshot */
    protected $txnSnapshot;

    /** @var Snapshot $commitTxnSnapshot */
    protected $commitTxnSnapshot;

    /** @var AbstractStorage $storage */
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
     * @return int
     */
    public function countRedoLogs(): int
    {
        return count($this->redoLogs);
    }

    /**
     * @return RedoLog|null
     */
    public function getLastRedoLog(): ?RedoLog
    {
        $redoLogsCount = $this->countRedoLogs();
        return ($redoLogsCount > 0) ? $this->redoLogs[$redoLogsCount - 1] : null;
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
     * @return int
     */
    public function countUndoLogs(): int
    {
        return count($this->undoLogs);
    }

    /**
     * @return UndoLog|null
     */
    public function getLastUndoLog(): ?UndoLog
    {
        $undoLogsCount = $this->countUndoLogs();
        return ($undoLogsCount > 0) ? $this->undoLogs[$undoLogsCount - 1] : null;
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
     * @param array $lockKeys
     * @return $this
     */
    public function removeLockKeys(array $lockKeys): self
    {
        $this->lockKeys = array_diff($this->lockKeys, $lockKeys);
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
     * @return Snapshot|null
     */
    public function getCommitTxnSnapshot(): ?Snapshot
    {
        return $this->commitTxnSnapshot;
    }

    /**
     * @param Snapshot $commitTxnSnapshot
     * @return $this
     */
    public function setCommitTxnSnapshot(Snapshot $commitTxnSnapshot): self
    {
        $this->commitTxnSnapshot = $commitTxnSnapshot;
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
     * @param int $status
     * @return bool
     */
    public function isStatus(int $status): bool
    {
        return $this->getStatus() === $status;
    }

    /**
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->isStatus(TxnConst::STATUS_PENDING);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isStatus(TxnConst::STATUS_ACTIVE);
    }

    /**
     * @return bool
     */
    public function isCommitted(): bool
    {
        return $this->isStatus(TxnConst::STATUS_COMMITTED);
    }

    /**
     * @return bool
     */
    public function isCanceled(): bool
    {
        return $this->isStatus(TxnConst::STATUS_CANCELED);
    }

    /**
     * @return false|string
     */
    public function __toString()
    {
        $commitTxnSnapshot = $this->getCommitTxnSnapshot();

        return json_encode([
            'status' => $this->getStatus(),
            'redo_logs' => array_map(fn(RedoLog $redoLog) => $redoLog->toArray(), $this->getRedoLogs()),
            'undo_logs' => array_map(fn(UndoLog $undoLog) => $undoLog->toArray(), $this->getUndoLogs()),
            'ts' => $this->getTs(),
            'commit_ts' => $this->getCommitTs(),
            'lock_keys' => $this->getLockKeys(),
            'txn_snapshot' => $this->getTxnSnapshot()->toArray(),
            'commit_txn_snapshot' => $commitTxnSnapshot ? $commitTxnSnapshot->toArray() : null,
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
                    //constrained by pk
                    $this->storage->add($log->getSchema(), $log->getRows());
                    break;
                case LogConst::OP_UPDATE_SCHEMA_DATA:
                    $this->storage->update($log->getSchema(), $log->getRowPkList(), $log->getRows());
                    break;
                case LogConst::OP_DEL_SCHEMA_DATA:
                    $this->storage->del($log->getSchema(), $log->getRowPkList());
                    break;
                case LogConst::OP_ADD_SCHEMA_META:
                    //constrained by schema
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

        $continue = true;

        //todo lock snapshot
        $txnSnapShot = $this->storage->getTxnSnapShot();
        if (is_null($txnSnapShot)) {
            $txnSnapShot = new Snapshot();
        }

        if (!$txnSnapShot->getIdList()->has($txnTs)) {
            $txnSnapShot->addIdList([$txnTs]);
            $continue = $this->storage->saveTxnSnapShot($txnSnapShot);
        }

        if ($continue) {
            if ($this->setStatus(TxnConst::STATUS_ACTIVE)
                ->setTxnSnapshot($txnSnapShot)
                ->setTs($txnTs)
                ->add()
            ) {
                return $txnTs;
            }
        }

        return 0;
    }

    /**
     * @throws \Throwable
     */
    public function commit()
    {
        $txnStatus = $this->getStatus();
        $txnTs = $this->getTs();

        if (!Arr::safeInArray($txnStatus, [TxnConst::STATUS_ACTIVE, TxnConst::STATUS_COMMITTED])) {
            throw new \Exception(
                'Txn[' . ((string)$txnTs) . '] status[' . ((string)$txnStatus) .
                '] not allowed for committing'
            );
        }

        $continue = true;

        if ($continue) {
            if ($txnStatus !== TxnConst::STATUS_COMMITTED) {
                $continue = $this->setStatus(TxnConst::STATUS_COMMITTED)
                    ->setCommitTs(Tso::txnCommitTs())
                    ->update();
            }
        }

        if ($continue) {
            $lockKeys = $this->getLockKeys();
            foreach ($lockKeys as $lockKey) {
                if (!Lock::txnUnLock($this, $lockKey)) {
                    $continue = false;
                }
            }
        }

        /**
         * Delete from global txn snapshot, to avoid creating new txn with txn snapshot contains this txn.
         *
         * To ensure that this txn can be located, add this txn to global txn gc snapshot before deleting
         * this txn from global txn snapshot.
         */

        if ($continue) {
            $txnGcSnapShot = $this->storage->getTxnGCSnapShot();
            if (is_null($txnGcSnapShot)) {
                $txnGcSnapShot = new Snapshot();
            }
            if (!$txnGcSnapShot->getIdList()->has($txnTs)) {
                $txnGcSnapShot->addIdList([$txnTs]);
                $continue = $this->storage->saveTxnGCSnapShot($txnGcSnapShot);
            }
        }

        //todo lock snapshot
        $currentTxnSnapShot = $this->storage->getTxnSnapShot();

        if ($continue) {
            if ($currentTxnSnapShot->getIdList()->has($txnTs)) {
                $currentTxnSnapShot->delIdList([$txnTs]);
                return $this->storage->saveTxnSnapShot($currentTxnSnapShot);
            }
        }

        if ($continue) {
            if (is_null($this->commitTxnSnapshot)) {
                $continue = $this->setCommitTxnSnapshot($currentTxnSnapShot)
                    ->update();
            }
        }

        if ($continue) {
            $commitTxnIdList = $this->getCommitTxnSnapshot()->getIdList()->iterator();
            $toDeleteTxn = true;
            foreach ($commitTxnIdList as $commitTxnId) {
                if ($commitTxnId === $txnTs) {
                    continue;
                }

                $commitTxn = self::fromTxnId($commitTxnId, $this->getStorage());
                if ((!is_null($commitTxn)) && (!$commitTxn->isCommitted())) {
                    $toDeleteTxn = false;
                }
            }

            //todo lock gc snapshot
            $txnGcSnapShot = $this->storage->getTxnGCSnapShot();
            if ($toDeleteTxn) { //The txn can be deleted, because other txns will not use the undo log of this txn
                if ($this->storage->delTxn($txnTs)) {
                    if ($txnGcSnapShot->getIdList()->has($txnTs)) {
                        $txnGcSnapShot->delIdList([$txnTs]);
                        $continue = $this->storage->saveTxnGCSnapShot($txnGcSnapShot);
                    }
                }
            }
        }

        return $continue;
    }

    /**
     * @return bool
     * @throws \Throwable
     */
    public function rollback()
    {
        $txnStatus = $this->getStatus();
        $txnTs = $this->getTs();

        if (!Arr::safeInArray($txnStatus, [TxnConst::STATUS_ACTIVE, TxnConst::STATUS_CANCELED])) {
            throw new \Exception(
                'Txn[' . ((string)$txnTs) . '] status[' . ((string)$txnStatus) .
                '] not allowed for rollback'
            );
        }

        //To avoid updating data after unlocking
        if ($txnStatus !== TxnConst::STATUS_CANCELED) {
            $this->executeUndoLogs();
        }

        $continue = true;

        if ($continue) {
            if ($txnStatus !== TxnConst::STATUS_CANCELED) {
                $this->setStatus(TxnConst::STATUS_CANCELED);
                $continue = $this->update();
            }
        }

        if ($continue) {
            $lockKeys = $this->getLockKeys();
            foreach ($lockKeys as $lockKey) {
                if (!Lock::txnUnLock($this, $lockKey)) {
                    $continue = false;
                }
            }
        }

        if ($continue) {
            $continue = $this->storage->delTxn($txnTs);
        }

        if ($continue) {
            //todo lock snapshot
            $txnSnapShot = $this->storage->getTxnSnapShot();
            if ($txnSnapShot->getIdList()->has($txnTs)) {
                $txnSnapShot->delIdList([$txnTs]);
                $continue = $this->storage->saveTxnSnapShot($txnSnapShot);
            }
        }

        return $continue;
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
            ->setCommitTs($txnArr['commit_ts'])
            ->setLockKeys($txnArr['lock_keys'])
            ->setTxnSnapshot(
                Snapshot::createFromArray($txnArr['txn_snapshot'])
            )
            ->setCommitTxnSnapshot(
                isset($txnArr['commit_txn_snapshot']) ?
                Snapshot::createFromArray($txnArr['commit_txn_snapshot']) : null
            )
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

    public static function compactTxnSnapshot()
    {
        $storage = StorageFactory::create();
        $txnSnapshot = $storage->getTxnSnapShot();
        if (is_null($txnSnapshot)) {
            return;
        }

        foreach ($txnSnapshot->getIdList()->iterator() as $txnId) {
            $txn = self::fromTxnId($txnId, $storage);
            if ($txn->isCommitted()) {
                $txn->commit();
            } elseif ($txn->isCanceled()) {
                $txn->rollback();
            } elseif ($txn->isActive()) {
                $lastRedoLog = $txn->getLastRedoLog();
                //todo txn timeout configuration
                if ($lastRedoLog->getTs() < strtotime('-10 minutes')) {
                    $txn->rollback();
                }
            }
        }
        //todo snapshot 不加锁，txn加锁，优化
    }

    public static function compactTxnGCSnapshot()
    {
        $storage = StorageFactory::create();
        $txnGCSnapshot = $storage->getTxnGCSnapShot();
        if (is_null($txnGCSnapshot)) {
            return;
        }

        foreach ($txnGCSnapshot->getIdList()->iterator() as $txnId) {
            $txn = self::fromTxnId($txnId, $storage);
            $txn->commit();
        }
        //todo snapshot 不加锁，txn加锁，优化
    }
}
