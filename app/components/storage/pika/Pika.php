<?php

namespace App\components\storage\pika;

use App\components\elements\condition\Condition;
use App\components\elements\condition\ConditionTree;
use App\components\elements\condition\Operand;
use App\components\math\OperatorHandler;
use App\components\storage\AbstractStorage;
use Co\Channel;
use SwFwLess\components\redis\RedisWrapper;
use SwFwLess\facades\RedisPool;

class Pika extends AbstractStorage
{
    protected $filterConditionCache = [];

    /**
     * @param $index
     * @param $callback
     * @return mixed
     * @throws \Throwable
     */
    protected function safeUseIndex($index, $callback) {
        try {
            return call_user_func_array($callback, [$index]);
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            RedisPool::release($index);
        }
    }

    /**
     * @param $schema
     * @return mixed|null
     * @throws \Throwable
     */
    public function getSchemaMetaData($schema)
    {
        $metaSchema = $this->openBtree('meta.schema');
        $schemaData = $this->safeUseIndex($metaSchema, function (RedisWrapper $metaSchema) use ($schema) {
            return $metaSchema->hGet('meta.schema', $schema);
        });
        if ($schemaData === false) {
            return null;
        }

        return json_decode($schemaData, true);
    }

    protected function filterConditionWithSchema($schema, $condition, $parentOperator = null)
    {
        if (is_null($condition)) {
            return null;
        }

        if ($condition instanceof Condition) {
            $operands = $condition->getOperands();

            foreach ($operands as $operand) {
                $operandType = $operand->getType();
                $operandValue = $operand->getValue();
                if ($operandType === 'colref') {
                    if (strpos($operandValue, '.')) {
                        list($operandSchema, $operandValue) = explode('.', $operandValue);
                        if ($operandSchema !== $schema) {
                            if (is_null($parentOperator)) {
                                return null;
                            } elseif ($parentOperator === 'and') {
                                return null;
                            } elseif ($parentOperator === 'not') {
                                $filteredConditionTree = new ConditionTree();
                                $filteredConditionTree->setLogicOperator('not');
                                $filteredCondition = (new Condition())->setOperator('=')
                                    ->addOperands(
                                        (new Operand())->setType('const')
                                            ->setValue(1)
                                    )
                                    ->addOperands(
                                        (new Operand())->setType('const')
                                            ->setValue(1)
                                    );
                                $filteredConditionTree->addSubConditions($filteredCondition);
                                return $filteredConditionTree;
                            } else {
                                return (new Condition())->setOperator('=')
                                    ->addOperands(
                                        (new Operand())->setType('const')
                                            ->setValue(1)
                                    )
                                    ->addOperands(
                                        (new Operand())->setType('const')
                                            ->setValue(1)
                                    );
                            }
                        }
                    }
                }
            }

            return $condition;
        }

        if ($condition instanceof ConditionTree) {
            $subConditions = $condition->getSubConditions();

            foreach ($subConditions as $i => $subCondition) {
                if (is_null($this->filterConditionWithSchema($schema, $subCondition, $condition->getLogicOperator()))) {
                    unset($subConditions[$i]);
                }
            }

            $subConditions = array_values($subConditions);

            if (count($subConditions) <= 0) {
                return null;
            }

            if (count($subConditions) === 1) {
                if ($condition->getLogicOperator() !== 'not') {
                    return $condition->setSubConditions($subConditions);
                } else {
                    return $subConditions[0];
                }
            } else {
                return $condition->setSubConditions($subConditions);
            }
        }

        return null;
    }

    /**
     * @param $schema
     * @param $condition
     * @param $limit
     * @param $indexSuggestions
     * @return array
     * @throws \Throwable
     */
    public function get($schema, $condition, $limit, $indexSuggestions)
    {
        $condition = $this->filterConditionWithSchema($schema, $condition);
        return $this->conditionFilter($schema, $condition, $limit, $indexSuggestions);
    }

    /**
     * @param $name
     * @param bool $new
     * @return bool|\SwFwLess\components\redis\RedisWrapper
     * @throws \Throwable
     */
    protected function openBtree($name, $new = false)
    {
        $redis = RedisPool::pick('pika');
        try {
            if (!$new) {
                if (!$redis->exists($name)) {
                    return false;
                }
            }

            return $redis;
        } catch (\Throwable $e) {
            RedisPool::release($redis);
            throw $e;
        }
    }

    /**
     * @param $schema
     * @param $limit
     * @return array|mixed
     * @throws \Throwable
     */
    protected function fetchAllPrimaryIndexData($schema, $limit)
    {
        $index = $this->openBtree($schema);
        if ($index === false) {
            return [];
        }

        $itLimit = 100;
        $offsetLimitCount = null;
        if (!is_null($limit)) {
            $offset = $limit['offset'] === '' ? 0 : $limit['offset'];
            $itLimit = $limitCount = $limit['rowcount'];
            $offsetLimitCount = $offset + $limitCount;
        }

        return $this->safeUseIndex($index, function (RedisWrapper $index) use (
            $schema, $itLimit, $offsetLimitCount
        ) {
            $indexData = [];
            $startKey = '';
            while (($result = $index->rawCommand(
                'pkhscanrange',
                $index->_prefix($schema),
                $startKey,
                '',
                'MATCH',
                '*',
                'LIMIT',
                $itLimit
            )) && isset($result[1])) {
                $skipFirst = ($startKey !== '');
                foreach ($result[1] as $key => $data) {
                    if ($skipFirst) {
                        if (in_array($key, [0, 1])) {
                            continue;
                        }
                    }

                    if ($key % 2 != 0) {
                        $indexData[] = json_decode($data, true);
                    } else {
                        $startKey = $data;
                    }
                }

                $resultCnt = count($result[1]);

                if ($skipFirst) {
                    if ($resultCnt <= 2) {
                        break;
                    }
                } else {
                    if ($resultCnt <= 0) {
                        break;
                    }
                }

                if (!is_null($offsetLimitCount)) {
                    if (count($indexData) >= $offsetLimitCount) {
                        break;
                    }
                }
            }

            return $indexData;
        });
    }

    /**
     * @param $id
     * @param $schema
     * @return mixed|null
     * @throws \Throwable
     */
    protected function fetchPrimaryIndexDataById($id, $schema)
    {
        $index = $this->openBtree($schema);
        if ($index === false) {
            return null;
        }

        $indexData = $this->safeUseIndex($index, function (RedisWrapper $index) use ($id, $schema) {
            return $index->hGet($schema, $id);
        });

        if ($indexData === false) {
            return null;
        } else {
            return json_decode($indexData, true);
        }
    }

    /**
     * @param $schema
     * @param $row
     * @param Condition $condition
     * @return bool
     * @throws \Throwable
     */
    protected function filterConditionByIndexData($schema, $row, Condition $condition)
    {
        $operands = $condition->getOperands();

        $operandValues = [];
        foreach ($operands as $operand) {
            $operandType = $operand->getType();
            $operandValue = $operand->getValue();
            if ($operandType === 'colref') {
                if (strpos($operandValue, '.')) {
                    list($operandSchema, $operandValue) = explode('.', $operandValue);
                    if ($operandSchema !== $schema) {
                        return true;
                    }
                }
                if (!array_key_exists($operandValue, $row)) {
                    $row = $this->fetchPrimaryIndexDataById($row['id'], $schema);
                }
                $operandValues[] = $row[$operandValue];
            } else {
                $operandValues[] = $operandValue;
            }
        }

        return (new OperatorHandler())->calculateOperatorExpr($condition->getOperator(), ...$operandValues);
    }

    /**
     * @param $schema
     * @param $row
     * @param ConditionTree $conditionTree
     * @return bool
     * @throws \Throwable
     */
    protected function filterConditionTreeByIndexData($schema, $row, ConditionTree $conditionTree)
    {
        $subConditions = $conditionTree->getSubConditions();
        $result = true;
        foreach ($subConditions as $i => $subCondition) {
            if ($subCondition instanceof Condition) {
                $subResult = $this->filterConditionByIndexData($schema, $row, $subCondition);
            } else {
                $subResult = $this->filterConditionTreeByIndexData($schema, $row, $subCondition);
            }
            if ($i === 0) {
                if ($conditionTree->getLogicOperator() === 'not') {
                    $result = !$subResult;
                } else {
                    $result = $subResult;
                }
            } else {
                switch ($conditionTree->getLogicOperator()) {
                    case 'and':
                        $result = ($result && $subResult);
                        break;
                    case 'or':
                        $result = ($result || $subResult);
                        break;
                    case 'not':
                        $result = ($result && (!$subResult));
                        break;
                }
            }
        }

        return $result;
    }

    /**
     * @param $schema
     * @param Condition $condition
     * @param $limit
     * @param $indexSuggestions
     * @param $isNot
     * @return array|mixed
     * @throws \Throwable
     */
    protected function filterBasicCompareCondition(
        $schema,
        Condition $condition,
        $limit,
        $indexSuggestions,
        $isNot
    )
    {
        $operatorHandler = new OperatorHandler($isNot);
        $conditionOperator = $condition->getOperator();
        $operands = $condition->getOperands();
        $operandValue1 = $operands[0]->getValue();
        $operandType1 = $operands[0]->getType();
        if ($operandType1 === 'colref') {
            if (strpos($operandValue1, '.')) {
                list(, $operandValue1) = explode('.', $operandValue1);
            }
        }
        $operandValue2 = $operands[1]->getValue();
        $operandType2 = $operands[1]->getType();
        if ($operandType2 === 'colref') {
            if (strpos($operandValue2, '.')) {
                list(, $operandValue2) = explode('.', $operandValue2);
            }
        }

        if ((($operandType1 === 'colref') && ($operandType2 === 'const')) ||
            (($operandType1 === 'const') && ($operandType2 === 'colref'))
        ) {
            if ((($operandType1 === 'colref') && ($operandType2 === 'const'))) {
                $field = $operandValue1;
                $conditionValue = $operandValue2;
            } else {
                $field = $operandValue2;
                $conditionValue = $operandValue1;
            }

            $index = false;
            $indexName = null;
            $usingPrimaryIndex = false;
            $suggestIndex = $indexSuggestions[$schema][$field] ?? null;
            if (!is_null($suggestIndex)) {
                $index = $this->openBtree($suggestIndex['indexName']);
                if ($index !== false) {
                    $indexName = $suggestIndex['indexName'];
                    $usingPrimaryIndex = $suggestIndex['primaryIndex'];
                }
            }
            if ($index === false) {
                $index = $this->openBtree($schema . '.' . $field);
                if ($index !== false) {
                    $indexName = $schema . '.' . $field;
                }
            }
            if ($index === false) {
                $usingPrimaryIndex = true;
                $index = $this->openBtree($schema);
                $indexName = $schema;
            }
            $itStart = '';
            $itEnd = '';

            $skipStart = false;
            $skipEnd = false;
            $skipValues = [];

            $itLimit = 100;
            $offset = null;
            $limitCount = null;
            $offsetLimitCount = null;
            if (!is_null($limit)) {
                $offset = $limit['offset'] === '' ? 0 : $limit['offset'];
                $itLimit = $limitCount = $limit['rowcount'];
                $offsetLimitCount = $offset + $limitCount;
                if ($skipStart) {
                    $offsetLimitCount += 1;
                }
                if ($skipEnd) {
                    $offsetLimitCount += 1;
                }
            }

            if ((!$usingPrimaryIndex) || ($field === 'id')) { //todo fetch primary key from schema meta data
                if ($conditionOperator === '=') {
                    if ($isNot) {
                        $skipValues[] = $conditionValue;
                    } else {
                        $itStart = $conditionValue;
                        $itEnd = $conditionValue;
                    }
                } elseif ($conditionOperator === '<') {
                    if ($isNot) {
                        $itStart = $conditionValue;
                    } else {
                        $itEnd = $conditionValue;
                        $skipEnd = true;
                    }
                } elseif ($conditionOperator === '<=') {
                    if ($isNot) {
                        $itStart = $conditionValue;
                        $skipStart = true;
                    } else {
                        $itEnd = $conditionValue;
                    }
                } elseif ($conditionOperator === '>') {
                    if ($isNot) {
                        $itEnd = $conditionValue;
                    } else {
                        $itStart = $conditionValue;
                        $skipStart = true;
                    }
                } elseif ($conditionOperator === '>=') {
                    if ($isNot) {
                        $itEnd = $conditionValue;
                        $skipEnd = true;
                    } else {
                        $itStart = $conditionValue;
                    }
                }
            }

            return $this->safeUseIndex($index, function (RedisWrapper $index) use (
                $usingPrimaryIndex, $itStart, $itEnd, $skipStart, $skipEnd,
                $itLimit, $offsetLimitCount, $indexName, $skipValues, $operatorHandler,
                $conditionOperator, $field, $conditionValue
            ) {
                $indexData = [];
                $skipFirst = false;
                while (($result = $index->rawCommand(
                    'pkhscanrange',
                    $index->_prefix($indexName),
                    $itStart,
                    $itEnd,
                    'MATCH',
                    '*',
                    'LIMIT',
                    $itLimit
                )) && isset($result[1])) {
                    foreach ($result[1] as $key => $data) {
                        if ($skipFirst && in_array($key, [0, 1])) {
                            continue;
                        }

                        if ($key % 2 != 0) {
                            if ($usingPrimaryIndex) {
                                $arrData = json_decode($data, true);
                                if (($field === 'id') || $operatorHandler->calculateOperatorExpr(
                                    $conditionOperator,
                                    ...[$arrData[$field], $conditionValue]
                                )) {
                                    $indexData[] = $arrData;
                                }
                            } else {
                                $indexData = array_merge($indexData, json_decode($data, true));
                            }
                        } else {
                            $itStart = $data;
                            if ((!$usingPrimaryIndex) || ($field === 'id')) { //todo fetch primary key from schema meta data
                                if (in_array($data, $skipValues)) {
                                    continue 2;
                                }
                            }
                        }
                    }

                    $resultCnt = count($result[1]);

                    //EOF
                    if ($skipFirst) {
                        if ($resultCnt <= 2) {
                            break;
                        }
                    } else {
                        if ($resultCnt <= 0) {
                            break;
                        }
                    }

                    if (($resultCnt > 1) && (!$skipFirst)) {
                        $skipFirst = true;
                    }

                    if (!is_null($offsetLimitCount)) {
                        if (count($indexData) >= $offsetLimitCount) {
                            break;
                        }
                    }
                }

                if ($skipStart) {
                    array_shift($indexData);
                }
                if ($skipEnd) {
                    array_pop($indexData);
                }

                return array_values($indexData);
            });
        } elseif ($operandType1 === 'const' && $operandType2 === 'const') {
            if ($operatorHandler->calculateOperatorExpr($conditionOperator, ...[$operandValue1, $operandValue2])) {
                return $this->fetchAllPrimaryIndexData($schema, $limit);
            } else {
                return [];
            }
        } else {
            return [];
        }
    }

    /**
     * @param $schema
     * @param Condition $condition
     * @param $limit
     * @param $indexSuggestions
     * @param $isNot
     * @return array|mixed
     * @throws \Throwable
     */
    protected function filterBetweenCondition(
        $schema,
        Condition $condition,
        $limit,
        $indexSuggestions,
        $isNot
    )
    {
        $operatorHandler = new OperatorHandler($isNot);
        $operands = $condition->getOperands();

        $operandValue1 = $operands[0]->getValue();
        $operandType1 = $operands[0]->getType();
        if ($operandType1 === 'colref') {
            if (strpos($operandValue1, '.')) {
                list(, $operandValue1) = explode('.', $operandValue1);
            }
        }

        $operandValue2 = $operands[1]->getValue();
        $operandType2 = $operands[1]->getType();
        if ($operandType2 === 'colref') {
            if (strpos($operandValue2, '.')) {
                list(, $operandValue2) = explode('.', $operandValue2);
            }
        }

        $operandValue3 = $operands[2]->getValue();
        $operandType3 = $operands[2]->getType();
        if ($operandType3 === 'colref') {
            if (strpos($operandValue3, '.')) {
                list(, $operandValue3) = explode('.', $operandValue3);
            }
        }

        if ($operandType1 === 'colref' && $operandType2 === 'const' && $operandType3 === 'const') {
            $index = false;
            $usingPrimaryIndex = false;
            $indexName = null;
            $suggestIndex = $indexSuggestions[$schema][$operandValue1] ?? null;
            if (!is_null($suggestIndex)) {
                $index = $this->openBtree($suggestIndex['indexName']);
                if ($index !== false) {
                    $indexName = $suggestIndex['indexName'];
                    $usingPrimaryIndex = $suggestIndex['primaryIndex'];
                }
            }
            if ($index === false) {
                $index = $this->openBtree($schema . '.' . $operandValue1);
                if ($index !== false) {
                    $indexName = $schema . '.' . $operandValue1;
                }
            }
            if ($index === false) {
                $usingPrimaryIndex = true;
                $index = $this->openBtree($schema);
                $indexName = $schema;
            }
            $itStart = '';
            $itEnd = '';
            $itLimit = 100;
            $offset = null;
            $limitCount = null;
            $offsetLimitCount = null;
            if (!is_null($limit)) {
                $offset = $limit['offset'] === '' ? 0 : $limit['offset'];
                $itLimit = $limitCount = $limit['rowcount'];
                $offsetLimitCount = $offset + $limitCount;
            }

            if ((!$usingPrimaryIndex) || ($operandValue1 === 'id')) { //todo fetch primary key from schema meta data
                if ($isNot) {
                    $splitConditionTree = new ConditionTree();
                    $splitConditionTree->setLogicOperator('and')
                        ->addSubConditions(
                            (new Condition())->setOperator('<')
                                ->addOperands($operands[0])
                                ->addOperands($operands[1])
                        )
                        ->addSubConditions(
                            (new Condition())->setOperator('>')
                                ->addOperands($operands[0])
                                ->addOperands($operands[2])
                        );
                    return $this->filterConditionTree(
                        $schema,
                        $splitConditionTree,
                        $limit,
                        $indexSuggestions
                    );
                } else {
                    $itStart = $operandValue2;
                    $itEnd = $operandValue3;
                }
            }

            return $this->safeUseIndex($index, function (RedisWrapper $index) use (
                $usingPrimaryIndex, $schema, $itStart, $itEnd, $itLimit, $offsetLimitCount, $operandValue1,
                $indexName, $operatorHandler, $operandValue2, $operandValue3
            ) {
                $indexData = [];
                $skipFirst = false;
                while (($result = $index->rawCommand(
                        'pkhscanrange',
                        $index->_prefix($indexName),
                        $itStart,
                        $itEnd,
                        'MATCH',
                        '*',
                        'LIMIT',
                        $itLimit
                    )) && isset($result[1])) {
                    foreach ($result[1] as $key => $data) {
                        if ($skipFirst && in_array($key, [0, 1])) {
                            continue;
                        }

                        if ($key % 2 != 0) {
                            if ($usingPrimaryIndex) {
                                $arrData = json_decode($data, true);
                                if (($operandValue1 === 'id') || $operatorHandler->calculateOperatorExpr( //todo fetch primary key from schema meta data
                                    'between',
                                    ...[$arrData[$operandValue1], $operandValue2, $operandValue3]
                                )) {
                                    $indexData[] = $arrData;
                                }
                            } else {
                                $indexData = array_merge($indexData, json_decode($data, true));
                            }
                        } else {
                            $itStart = $data;
                        }
                    }

                    $resultCnt = count($result[1]);

                    //EOF
                    if ($skipFirst) {
                        if ($resultCnt <= 2) {
                            break;
                        }
                    } else {
                        if ($resultCnt <= 0) {
                            break;
                        }
                    }

                    if (($resultCnt > 1) && (!$skipFirst)) {
                        $skipFirst = true;
                    }

                    if (!is_null($offsetLimitCount)) {
                        if (count($indexData) >= $offsetLimitCount) {
                            break;
                        }
                    }
                }

                return array_values($indexData);
            });
        } else {
            return [];
        }
    }

    /**
     * @param $schema
     * @param Condition $condition
     * @param $limit
     * @param $indexSuggestions
     * @param bool $isNot
     * @return array|mixed
     * @throws \Throwable
     */
    protected function filterCondition(
        $schema,
        Condition $condition,
        $limit,
        $indexSuggestions,
        bool $isNot = false
    )
    {
        $operandsCacheKey = [];
        foreach ($condition->getOperands() as $operand) {
            $operandsCacheKey[] = [
                'value' => $operand->getValue(),
                'type' => $operand->getType(),
            ];
        }
        $cacheKey = json_encode([
            'operator' => $condition->getOperator(),
            'operands' => $operandsCacheKey,
        ]);
        if (array_key_exists($cacheKey, $this->filterConditionCache)) {
            return $this->filterConditionCache[$cacheKey];
        }

        $conditionOperator = $condition->getOperator();
        if (in_array($conditionOperator, ['<', '<=', '=', '>', '>='])) {
            return $this->filterConditionCache[$cacheKey] =
                $this->filterBasicCompareCondition($schema, $condition, $limit, $indexSuggestions, $isNot);
        } elseif ($conditionOperator === 'between') {
            return $this->filterConditionCache[$cacheKey] =
                $this->filterBetweenCondition($schema, $condition, $limit, $indexSuggestions, $isNot);
        }

        return $this->filterConditionCache[$cacheKey] = [];

        //todo support more operators
    }

    /**
     * @param $schema
     * @param ConditionTree $conditionTree
     * @param $limit
     * @param $indexSuggestions
     * @return array
     * @throws \Throwable
     */
    protected function filterConditionTree(
        $schema,
        ConditionTree $conditionTree,
        $limit,
        $indexSuggestions
    )
    {
        $isNot = $conditionTree->getLogicOperator() === 'not';

        $result = [];

        $subConditions = $conditionTree->getSubConditions();
        $subConditionCount = count($subConditions);

        $channel = new Channel($subConditionCount);

        foreach ($subConditions as $i => $subCondition) {
            go(function () use (
                $subCondition, $schema, $limit, $indexSuggestions, $isNot, $channel
            ) {
                if ($subCondition instanceof Condition) {
                    $subResult = $this->filterCondition($schema, $subCondition, $limit, $indexSuggestions, $isNot);
                } else {
                    if ($isNot && ($subCondition->getLogicOperator() === 'not')) {
                        $subResult = [];
                        foreach ($subCondition->getSubConditions() as $j => $subSubCondition) {
                            if ($subSubCondition instanceof Condition) {
                                $subResult = array_merge($subResult, $this->filterCondition(
                                    $schema,
                                    $subSubCondition,
                                    $limit,
                                    $indexSuggestions
                                ));
                            } else {
                                $subResult = array_merge($subResult, $this->filterConditionTree(
                                    $schema,
                                    $subSubCondition,
                                    $limit,
                                    $indexSuggestions
                                ));
                            }
                        }
                    } else {
                        $subResult = $this->filterConditionTree($schema, $subCondition, $limit, $indexSuggestions);
                    }
                }

                $channel->push($subResult);
            });
        }

        for ($i = 0; $i < $subConditionCount; ++$i) {
            $result = array_merge($result, $channel->pop());
        }

        $idMap = [];
        foreach ($result as $i => $row) {
            if (in_array($row['id'], $idMap)) {
                unset($result[$i]);
            } else {
                $idMap[] = $row['id'];
            }
        }

        return array_values($result);
    }

    /**
     * Fetching index data by single condition, then filtering index data by all conditions.
     *
     * @param $schema
     * @param $condition
     * @param $limit
     * @param $indexSuggestions
     * @return array
     * @throws \Throwable
     */
    protected function conditionFilter($schema, $condition, $limit, $indexSuggestions)
    {
        if (!is_null($condition)) {
            if ($condition instanceof Condition) {
                $indexData = $this->filterCondition($schema, $condition, $limit, $indexSuggestions);
            } else {
                $indexData = $this->filterConditionTree($schema, $condition, $limit, $indexSuggestions);
                foreach ($indexData as $i => $row) {
                    if (!$this->filterConditionTreeByIndexData($schema, $row, $condition)) {
                        unset($indexData[$i]);
                    }
                }
            }

            foreach ($indexData as $i => $row) {
                $indexData[$i] = $this->fetchPrimaryIndexDataById($row['id'], $schema);
            }
        } else {
            $indexData = $this->fetchAllPrimaryIndexData($schema, $limit);
        }

        foreach ($indexData as $i => $row) {
            foreach ($row as $column => $value) {
                $row[$schema . '.' . $column] = $value;
            }
            $indexData[$i] = $row;
        }

        return array_values($indexData);
    }
}
