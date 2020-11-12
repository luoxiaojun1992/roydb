<?php

namespace App\components\utils\datatype;

class Boolean
{
    public static function rawVal($boolVal)
    {
        if (is_bool($boolVal)) {
            return $boolVal;
        }

        if (strtolower($boolVal) === 'true') {
            return true;
        } else {
            return false;
        }
    }
}
