<?php

namespace App\components\utils\datatype;

use SwFwLess\components\utils\Arr;

class Type
{
    const TYPE_STR = 'string';
    const TYPE_DOUBLE = 'double';
    const TYPE_INTEGER = 'integer';
    const TYPE_BOOL = 'boolean';
    const TYPE_NULL = 'null';
    const TYPE_UNKNOWN = 'unknown';

    public static function getName($val)
    {
        if (static::isStr($val)) {
            return static::TYPE_STR;
        } elseif (static::isDouble($val)) {
            return static::TYPE_DOUBLE;
        } elseif (static::isInteger($val)) {
            return static::TYPE_INTEGER;
        } elseif (static::isBool($val)) {
            return static::TYPE_BOOL;
        } elseif (static::isNull($val)) {
            return static::TYPE_NULL;
        } else {
            return is_string($val) ? static::TYPE_STR : self::TYPE_UNKNOWN;
        }
    }

    public static function rawVal($val)
    {
        $rawVal = $val;

        $typeName = static::getName($val);
        if ($typeName === static::TYPE_STR) {
            $rawVal = Str::rawVal($val);
        } elseif ($typeName === static::TYPE_DOUBLE) {
            $rawVal = doubleval($val);
        } elseif ($typeName === static::TYPE_INTEGER) {
            $rawVal = intval($val);
        } elseif ($typeName === static::TYPE_BOOL) {
            $rawVal = Boolean::rawVal($val);
        } elseif ($typeName === static::TYPE_NULL) {
            $rawVal = null;
        }

        return $rawVal;
    }

    public static function isStr($val)
    {
        if (!is_string($val)) {
            return false;
        }

        $isStr = false;

        if (strpos($val, '"') === 0) {
            $isStr = true;
        } elseif (strpos($val, '\'') === 0) {
            $isStr = true;
        }

        if (strpos($val, '"') === (strlen($val) - 1)) {
            $isStr = true;
        } elseif (strpos($val, '\'') === (strlen($val) - 1)) {
            $isStr = true;
        }

        return $isStr;
    }

    public static function isDouble($val)
    {
        if (is_double($val) || is_float($val)) {
            return true;
        }

        $isDouble = false;

        if (is_string($val)) {
            if (is_numeric($val)) {
                if (strpos($val, '.') !== false) {
                    $isDouble = true;
                }
            }
        }

        return $isDouble;
    }

    public static function isInteger($val)
    {
        if (is_integer($val)) {
            return true;
        }

        $isInteger = false;

        if (is_string($val)) {
            if (is_numeric($val)) {
                if (strpos($val, '.') === false) {
                    $isInteger = true;
                }
            }
        }

        return $isInteger;
    }

    public static function isBool($val)
    {
        if (is_bool($val)) {
            return true;
        }

        $isBool = false;

        if (is_string($val)) {
            if (Arr::safeInArray(strtolower($val), ['true', 'false'])) {
                $isBool = true;
            }
        }

        return $isBool;
    }

    public static function isNull($val)
    {
        if (is_null($val)) {
            return true;
        }

        $isNull = false;

        if (is_string($val)) {
            if (Arr::safeInArray(strtolower($val), ['null'])) {
                $isNull = true;
            }
        }

        return $isNull;
    }
}
