<?php

namespace App\services;

use App\services\roydb\QueryClient;
use App\services\roykv\KvClient;
use App\services\tikv\TiKVClient;
use Roydb\SelectRequest;
use Roykv\BeginRequest;
use Roykv\CountRequest;
use Roykvtikv\SetRequest;
use SwFwLess\components\http\Response;
use SwFwLess\facades\Log;
use SwFwLess\facades\RedisPool;
use SwFwLess\services\BaseService;

class QueryService extends BaseService
{
    public function select()
    {
//        $countReply = (new KvClient())->Count(
//            (new CountRequest())->setStartKey('data.schema.test1::')
//                ->setEndKey('')
//                ->setKeyPrefix('data.schema.test1::')
//        );
//
//        var_dump($countReply->getCount());

//        $client = new TxnClient();
//        $reply = $client->Begin(new BeginRequest());
//        var_dump($reply->getTxnId());
//        return [
//            'code' => 0,
//            'msg' => 'ok',
//            'data' => [
//            ],
//        ];

//        $partitions = [];
//        $partitions[] = [
//            'lower' => '',
//            'upper' => 0,
//        ];
//        for ($start = 1, $end = 10000; $end <= 100000; $start = $start + 10000, $end = $end + 10000) {
//            $partitions[] = [
//                'lower' => $start,
//                'upper' => $end,
//            ];
//        }
//        $partitions[] = [
//            'lower' => 100001,
//            'upper' => '',
//        ];
//
//        $kvClient = new TiKVClient();
//            for ($i = 1; $i < 10; ++$i) {
//                $targetPartitionIndex = null;
//                foreach ($partitions as $partitionIndex => $partition) {
//                    if ((($partition['lower'] === '') || ($i >= $partition['lower'])) &&
//                        (($partition['upper'] === '') || ($i <= $partition['upper']))
//                    ) {
//                        $targetPartitionIndex = $partitionIndex;
//                        break;
//                    }
//                }
//
//                $firstAlphabet = chr(ord('a') + ($i % 26));
//                $kvClient->Set(
//                    (new SetRequest())->setKey('data.schema.test2.name::' . $firstAlphabet . 'oo')
//                        ->setValue(json_encode([['id' => $i]]))
//                );
//                $kvClient->Set(
//                    (new SetRequest())->setKey('data.schema.test2.type::1')
//                        ->setValue(json_encode([['id' => $i]]))
//                );
//                $kvClient->Set(
//                    (new SetRequest())->setKey('data.schema.test2::' . ((string)$i))
//                        ->setValue(json_encode(['id' => $i, 'type' => 1, 'name' => $firstAlphabet . 'oo']))
//                );
//                $kvClient->Set(
//                    (new SetRequest())->setKey(
//                        'data.schema.test2.partition.' . ((string)$targetPartitionIndex) . '::' . ((string)$i))
//                        ->setValue(json_encode(['id' => $i, 'type' => 1, 'name' => $firstAlphabet . 'oo']))
//                );
//                $kvClient->Set(
//                    (new SetRequest())->setKey(
//                        'data.schema.test1.name' . '::' . $firstAlphabet . 'oo')
//                        ->setValue(json_encode([['id' => $i]]))
//                );
//                $kvClient->Set(
//                    (new SetRequest())->setKey(
//                        'data.schema.test1.type' . '::1')
//                        ->setValue(json_encode([['id' => $i]]))
//                );
//                $kvClient->Set(
//                    (new SetRequest())->setKey(
//                        'data.schema.test1' . '::' . ((string)$i))
//                        ->setValue(json_encode(['id' => $i, 'type' => 1, 'name' => $firstAlphabet . 'oo']))
//                );
//                $kvClient->Set(
//                    (new SetRequest())->setKey(
//                        'data.schema.test1.partition.' . ((string)$targetPartitionIndex) . '::' . ((string)$i))
//                        ->setValue(json_encode(['id' => $i, 'type' => 1, 'name' => $firstAlphabet . 'oo']))
//                );
//            }
//
//            $kvClient->Set(
//                (new SetRequest())->setKey(
//                    'meta.schema::test1'
//                )->setValue(
//                    json_encode([
//                        'pk' => 'id',
//                        'columns' => [
//                            [
//                                'name' => 'id',
//                                'type' => 'int',
//                                'length' => 11,
//                                'default' => null,
//                                'allow_null' => false,
//                            ],
//                            [
//                                'name' => 'type',
//                                'type' => 'int',
//                                'length' => 11,
//                                'default' => 0,
//                                'allow_null' => false,
//                            ],
//                            [
//                                'name' => 'name',
//                                'type' => 'varchar',
//                                'length' => 255,
//                                'default' => '',
//                                'allow_null' => false,
//                            ],
//                        ],
//                        'index' => [
//                            [
//                                'name' => 'type',
//                                'columns' => ['type'],
//                                'unique' => false,
//                            ],
//                            [
//                                'name' => 'name',
//                                'columns' => ['name'],
//                                'unique' => false,
//                            ],
//                        ],
//                        'partition' => [
//                            'key' => 'id',
//                            'range' => $partitions,
//                        ],
//                    ])
//                )
//            );
//
//            $kvClient->Set(
//                (new SetRequest())->setKey(
//                    'meta.schema::test2'
//                )->setValue(
//                    json_encode([
//                        'pk' => 'id',
//                        'columns' => [
//                            [
//                                'name' => 'id',
//                                'type' => 'int',
//                                'length' => 11,
//                                'default' => null,
//                                'allow_null' => false,
//                            ],
//                            [
//                                'name' => 'type',
//                                'type' => 'int',
//                                'length' => 11,
//                                'default' => 0,
//                                'allow_null' => false,
//                            ],
//                            [
//                                'name' => 'name',
//                                'type' => 'varchar',
//                                'length' => 255,
//                                'default' => '',
//                                'allow_null' => false,
//                            ],
//                        ],
//                        //not using assoc array because of the confusion about digital key and numeric key
//                        'index' => [
//                            [
//                                'name' => 'type',
//                                'columns' => ['type'],
//                                'unique' => false,
//                                'cardinality' => 0,
//                            ],
//                            [
//                                'name' => 'name',
//                                'columns' => ['name'],
//                                'unique' => false,
//                                'cardinality' => 0,
//                            ],
//                        ],
//                        'partition' => [
//                            'key' => 'id',
//                            'range' => $partitions,
//                        ],
//                    ])
//                )
//            );
//
//            return [
//                'code' => 0,
//                'msg' => 'ok',
//                'data' => [
//                    'result_set' => [],
//                ],
//            ];


//        $index = RedisPool::pick('pika');
//        $result = $index->rawCommand(
//            'pkhscanrange',
//            $index->_prefix('test1'),
//            '',
//            '',
//            'MATCH',
//            '*',
//            'LIMIT',
//            100
//        );
//        foreach ($result[1] as $key => $data) {
//            if ($key % 2 != 0) {
//                var_dump($data);
//            }
//        }
//        RedisPool::release($index);
//        return [
//            'code' => 0,
//            'msg' => 'ok',
//            'data' => $result,
//        ];

        $start = microtime(true);
        $sql = $this->request->post('sql');
        $selectResponse = (new QueryClient())->Select(
            (new SelectRequest())->setSql($sql)
        );

        if (!$selectResponse) {
            return [
                'code' => -1,
                'msg' => 'failed',
                'data' => [
                    'result_set' => [],
                ],
            ];
        }

        $resultSet = [];
        $rows = $selectResponse->getRowData();
        foreach ($rows as $row) {
            $rowData = [];
            foreach ($row->getField() as $field) {
                $key = $field->getKey();
                $valueType = $field->getValueType();
                if ($valueType === 'integer') {
                    $rowData[$key] = $field->getIntValue();
                } elseif ($valueType === 'double') {
                    $rowData[$key] = $field->getDoubleValue();
                } elseif ($valueType === 'string') {
                    $rowData[$key] = $field->getStrValue();
                }
            }
            $resultSet[] = $rowData;
        }

        return [
            'code' => 0,
            'msg' => 'ok',
            'data' => [
                'result_set' => $resultSet,
                'time_usage' => microtime(true) - $start,
            ],
        ];
    }
}
