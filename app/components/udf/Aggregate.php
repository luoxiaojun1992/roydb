<?php

namespace App\components\udf;

use App\components\elements\Aggregation;
use App\components\elements\Column;

class Aggregate
{
    public static function count($parameters, $row, $resultSet)
    {
        /** @var Column $column */
        $column = $parameters[0];

        $columnType = $column->getType();
        $columnValue = $column->getValue();

        if ($row instanceof Aggregation) {
            $resultSet = $row->getRows();
        }

        if ($columnType === 'const') {
            return count($resultSet);
        } else {
            if ($columnValue === '*') {
                return count($resultSet);
            } else {
                return count(array_filter(array_column($resultSet, $columnValue), function ($value) {
                    return !is_null($value);
                }));
            }
        }
    }

    /**
     * @param $parameters
     * @param $row
     * @param $resultSet
     * @return mixed
     * @throws \Exception
     */
    public static function max($parameters, $row, $resultSet)
    {
        /** @var Column $column */
        $column = $parameters[0];

        $columnType = $column->getType();
        $columnValue = $column->getValue();
        if ($columnType === 'const') {
            throw new \Exception('Unsupported const param passed to max function');
        } else {
            if ($columnValue === '*') {
                throw new \Exception('Unsupported column named as \'*\' passed to max function');
            }

            if ($row instanceof Aggregation) {
                return max(array_column($row->getRows(), $columnValue));
            } else {
                return max(array_column($resultSet, $columnValue));
            }
        }
    }

    /**
     * @param $parameters
     * @param $row
     * @param $resultSet
     * @return mixed
     * @throws \Exception
     */
    public static function min($parameters, $row, $resultSet)
    {
        /** @var Column $column */
        $column = $parameters[0];

        $columnType = $column->getType();
        $columnValue = $column->getValue();
        if ($columnType === 'const') {
            throw new \Exception('Unsupported const param passed to min function');
        } else {
            if ($columnValue === '*') {
                throw new \Exception('Unsupported column named as \'*\' passed to min function');
            }

            if ($row instanceof Aggregation) {
                return min(array_column($row->getRows(), $columnValue));
            } else {
                return min(array_column($resultSet, $columnValue));
            }
        }
    }

    /**
     * @param $parameters
     * @param $row
     * @param $resultSet
     * @return mixed
     * @throws \Exception
     */
    public static function first($parameters, $row, $resultSet)
    {
        /** @var Column $column */
        $column = $parameters[0];

        $columnType = $column->getType();
        $columnValue = $column->getValue();
        if ($columnType === 'const') {
            throw new \Exception('Unsupported const param passed to first function');
        } else {
            if ($columnValue === '*') {
                if ($row instanceof Aggregation) {
                    return $row->getFirstRow();
                } else {
                    return $resultSet[0];
                }
            }
            if ($row instanceof Aggregation) {
                return $row->getFirstRow()[$columnValue];
            } else {
                return $resultSet[0][$columnValue];
            }
        }
    }
}
