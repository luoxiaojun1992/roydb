<?php

namespace App\components\storage;

use App\components\transaction\Snapshot;

abstract class AbstractStorage
{
    //TODO builder、factory
    protected $storageEngine;

    abstract public function get($schema, $condition, $limit, $indexSuggestions, $usedColumns);

    abstract public function getSchemaMetaData($schema);

    abstract public function addSchemaMetaData($schema, $metaData);

    abstract public function delSchemaMetaData($schema);

    abstract public function updateSchemaMetaData($schema, $metaData);

    abstract public function countAll($schema);

    abstract public function add($schema, $rows);

    abstract public function del($schema, $pkList);

    abstract public function update($schema, $pkList, $updateRow);

    abstract public function estimateIndexCardinality($schema, $index);

    abstract public function getTxn($txnId);

    abstract public function addTxn($txnId, $txnJson);

    abstract public function delTxn($txnId);

    abstract public function updateTxn($txnId, $txnJson);

    abstract public function getTxnSnapShot() : ?Snapshot;

    abstract public function saveTxnSnapShot(Snapshot $snapshot);

    abstract public function getTxnGCSnapShot() : ?Snapshot;

    abstract public function saveTxnGCSnapShot(Snapshot $gcSnapshot);
}
