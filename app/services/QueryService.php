<?php

namespace App\services;

use App\components\optimizers\CostBasedOptimizer;
use App\components\optimizers\RulesBasedOptimizer;
use App\components\Parser;
use App\components\plans\Plan;
use App\components\storage\pika\Pika;
use SwFwLess\components\http\Response;
use SwFwLess\facades\Log;
use SwFwLess\facades\RedisPool;
use SwFwLess\services\BaseService;

class QueryService extends BaseService
{
    public function select()
    {
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
//        $redis = RedisPool::pick('pika');
//        try {
//            for ($i = 1; $i < 100000; ++$i) {
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
//                $firstAlphabet = chr(ord('a') + ($i % 25));
//                $redis->hSet('test2.name', $firstAlphabet . 'oo', json_encode([['id' => $i]]));
//                $redis->hSet('test2', $i, json_encode(['id' => $i, 'type' => 1, 'name' => $firstAlphabet . 'oo']));
//                $redis->hSet(
//                    'test2.partition.' . ((string)$targetPartitionIndex),
//                    $i,
//                    json_encode(['id' => $i, 'type' => 1, 'name' => $firstAlphabet . 'oo'])
//                );
//                $redis->hSet('test1.name', $firstAlphabet . 'oo', json_encode([['id' => $i]]));
//                $redis->hSet('test1', $i, json_encode(['id' => $i, 'type' => 1, 'name' => $firstAlphabet . 'oo']));
//                $redis->hSet(
//                    'test1.partition.' . ((string)$targetPartitionIndex),
//                    $i,
//                    json_encode(['id' => $i, 'type' => 1, 'name' => $firstAlphabet . 'oo'])
//                );
//            }
//
//            $redis->hSet('meta.schema', 'test1', json_encode([
//                'pk' => 'id',
//                'columns' => [
//                    [
//                        'name' => 'id',
//                        'type' => 'int',
//                        'length' => 11,
//                        'default' => null,
//                        'allow_null' => false,
//                    ],
//                    [
//                        'name' => 'type',
//                        'type' => 'int',
//                        'length' => 11,
//                        'default' => 0,
//                        'allow_null' => false,
//                    ],
//                    [
//                        'name' => 'name',
//                        'type' => 'varchar',
//                        'length' => 255,
//                        'default' => '',
//                        'allow_null' => false,
//                    ],
//                ],
//                'index' => [
//                    [
//                        'name' => 'name',
//                        'columns' => ['name'],
//                        'unique' => false,
//                    ],
//                ],
//                'partition' => [
//                    'key' => 'id',
//                    'range' => $partitions,
//                ],
//            ]));
//            $redis->hSet('meta.schema', 'test2', json_encode([
//                'pk' => 'id',
//                'columns' => [
//                    [
//                        'name' => 'id',
//                        'type' => 'int',
//                        'length' => 11,
//                        'default' => null,
//                        'allow_null' => false,
//                    ],
//                    [
//                        'name' => 'type',
//                        'type' => 'int',
//                        'length' => 11,
//                        'default' => 0,
//                        'allow_null' => false,
//                    ],
//                    [
//                        'name' => 'name',
//                        'type' => 'varchar',
//                        'length' => 255,
//                        'default' => '',
//                        'allow_null' => false,
//                    ],
//                ],
//                'index' => [
//                    [
//                        'name' => 'name',
//                        'columns' => ['name'],
//                        'unique' => false,
//                    ],
//                ],
//                'partition' => [
//                    'key' => 'id',
//                    'range' => $partitions,
//                ],
//            ]));
//            var_dump($redis->hGet('meta.schema', 'test1'));
//            var_dump($redis->hGet('meta.schema', 'test2'));
//
//            return [
//                'code' => 0,
//                'msg' => 'ok',
//                'data' => [
//                    'result_set' => [],
//                ],
//            ];
//        } catch (\Throwable $e) {
//            throw $e;
//        } finally {
//            RedisPool::release($redis);
//        }
//        return;


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
        $ast = Parser::fromSql($sql)->parseAst();
        $plan = Plan::create($ast, new Pika());
        $plan = RulesBasedOptimizer::fromPlan($plan)->optimize();
        $plan = CostBasedOptimizer::fromPlan($plan)->optimize();
        $resultSet = $plan->execute();

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
