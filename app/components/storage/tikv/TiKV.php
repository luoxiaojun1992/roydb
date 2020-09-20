<?php

namespace App\components\storage\tikv;

use App\components\storage\KvStorage;
use App\components\transaction\Snapshot;
use App\services\tikv\TiKVClient;
use Roykvtikv\CountRequest;
use Roykvtikv\DelRequest;
use Roykvtikv\GetAllRequest;
use Roykvtikv\GetRequest;
use Roykvtikv\MGetRequest;
use Roykvtikv\ScanRequest;
use Roykvtikv\SetRequest;

class TiKV extends KvStorage
{
    /**
     * @return TiKVClient
     */
    protected function getKvClient()
    {
        return new TiKVClient();
    }

    /**
     * @param TiKVClient $kvClient
     * @param $schemaName
     * @return null|string
     */
    protected function metaSchemaGet($kvClient, $schemaName)
    {
        $metaSchema = null;

        $getReply = $kvClient->Get((new GetRequest())->setKey('meta.schema::' . $schemaName));
        if ($getReply) {
            $metaSchema = $getReply->getValue() ?: null;
        }

        return $metaSchema;
    }

    /**
     * @param TiKVClient $kvClient
     * @param $schemaName
     * @param $schemaMeta
     * @return false
     */
    protected function metaSchemaSet($kvClient, $schemaName, $schemaMeta)
    {
        $setReply = $kvClient->Set(
            (new SetRequest())->setKey('meta.schema::' . $schemaName)
            ->setValue($schemaMeta)
        );
        if ($setReply) {
            return $setReply->getResult();
        }

        return false;
    }

    /**
     * @param TiKVClient $kvClient
     * @param $schemaName
     * @return int
     */
    protected function metaSchemaDel($kvClient, $schemaName)
    {
        $delReply = $kvClient->Del(
            (new DelRequest())->setKeys(['meta.schema::' . $schemaName])
        );
        if ($delReply) {
            return $delReply->getDeleted();
        }
        return 0;
    }

    /**
     * @param TiKVClient $kvClient
     * @param $indexName
     * @return array
     */
    protected function dataSchemaGetAll($kvClient, $indexName)
    {
        $values = [];
        $getAllReply = $kvClient->GetAll((new GetAllRequest())->setKeyPrefix('data.schema.' . $indexName . '::'));
        if ($getAllReply) {
            $data = $getAllReply->getData();
            foreach ($data as $key => $value) {
                $values[] = $value;
            }
        }

        return $values;
    }

    /**
     * @param TiKVClient $kvClient
     * @param $id
     * @param $schema
     * @return null|string
     */
    protected function dataSchemaGetById($kvClient, $id, $schema)
    {
        $data = null;
        if (is_int($id)) {
            $id = (string)$id;
        }

        $getReply = $kvClient->Get((new GetRequest())->setKey('data.schema.' . $schema . '::' . $id));
        if ($getReply) {
            $data = $getReply->getValue() ?: null;
        }

        return $data;
    }

    /**
     * @param TiKVClient $kvClient
     * @param $indexName
     * @param $startKey
     * @param $endKey
     * @param $limit
     * @param callable $callback
     * @param bool $skipFirst
     */
    protected function dataSchemaScan(
        $kvClient, $indexName, &$startKey, &$endKey, $limit, $callback, &$skipFirst = false
    )
    {
        while (($scanReply = $kvClient->Scan(
            (new ScanRequest())->setStartKey('data.schema.' . $indexName . '::' . ((string)$startKey))
                ->setStartKeyType(gettype($startKey))
                ->setEndKey((((string)$endKey) === '') ? '' : ('data.schema.' . $indexName . '::' . ((string)$endKey)))
                ->setEndKeyType(gettype($endKey))
                ->setKeyPrefix('data.schema.' . $indexName . '::')
                ->setLimit($limit)
        ))) {
            $data = [];
            $resultCount = 0;
            foreach ($scanReply->getData() as $i => $item) {
                ++$resultCount;

                if ($skipFirst && ($i === 0)) {
                    continue;
                }

                $key = substr($item->getKey(), strlen('data.schema.' . $indexName . '::'));
                $data[$key] = $item->getValue();
            }

            if (call_user_func_array($callback, [$data, $resultCount]) === false) {
                break;
            }
        }
    }

    /**
     * @param TiKVClient $kvClient
     * @param $schema
     * @param $idList
     * @return array
     */
    protected function dataSchemaMGet($kvClient, $schema, $idList)
    {
        $values = [];

        array_walk($idList, function (&$val) use ($schema) {
            $val = 'data.schema.' . $schema . '::' . ((string)$val);
        });

        $mGetReply = $kvClient->MGet((new MGetRequest())->setKeys($idList));
        if ($mGetReply) {
            $data = $mGetReply->getData();
            foreach ($data as $key => $value) {
                $values[] = $value;
            }
        }

        return $values;
    }

    /**
     * @param TiKVClient $kvClient
     * @param $schema
     * @return mixed
     */
    protected function dataSchemaCountAll($kvClient, $schema)
    {
        $countReply = $kvClient->Count(
            (new CountRequest())->setStartKey('data.schema.' . $schema . '::')
                ->setStartKeyType(gettype(''))
                ->setEndKey('')
                ->setEndKeyType(gettype(''))
                ->setKeyPrefix('data.schema.' . $schema . '::')
        );

        if ($countReply) {
            return $countReply->getCount();
        }

        return 0;
    }

    /**
     * @param TiKVClient $kvClient
     * @param $indexName
     * @param $id
     * @param $value
     * @return bool
     */
    protected function dataSchemaSet($kvClient, $indexName, $id, $value)
    {
        $setReply = $kvClient->Set(
            (new SetRequest())->setKey('data.schema.' . $indexName . '::' . $id)
                ->setValue($value)
        );

        if ($setReply) {
            return $setReply->getResult();
        }

        return false;
    }

    /**
     * @param TiKVClient $kvClient
     * @param $indexName
     * @param $id
     * @return bool
     */
    protected function dataSchemaDel($kvClient, $indexName, $id)
    {
        $delReply = $kvClient->Del(
            (new DelRequest())->setKeys(['data.schema.' . $indexName . '::' . $id])
        );

        if ($delReply) {
            return $delReply->getDeleted() > 0;
        }

        return false;
    }

    protected function metaTxnGet($kvClient, $txnId)
    {
        // TODO: Implement metaTxnGet() method.
    }

    protected function metaTxnSet($kvClient, $txnId, $txnJson)
    {
        // TODO: Implement metaTxnSet() method.
    }

    protected function metaTxnDel($kvClient, $txnId)
    {
        // TODO: Implement metaTxnDel() method.
    }

    protected function metaTxnSnapshotGet($kvClient)
    {
        // TODO: Implement metaTxnSnapshotGet() method.
    }

    protected function metaTxnSnapshotSet($kvClient, Snapshot $snapshot)
    {
        // TODO: Implement metaTxnSnapshotSet() method.
    }

    protected function metaTxnGCSnapshotGet($kvClient)
    {
        // TODO: Implement metaTxnGCSnapshotGet() method.
    }

    protected function metaTxnGCSnapshotSet($kvClient, Snapshot $snapshot)
    {
        // TODO: Implement metaTxnGCSnapshotSet() method.
    }
}
