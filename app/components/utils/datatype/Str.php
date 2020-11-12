<?php

namespace App\components\utils\datatype;

class Str
{
    public static function rawVal(string $quotedStr)
    {
        if (strpos($quotedStr, '"') === 0) {
            $quotedStr = substr($quotedStr, 1);
        } elseif (strpos($quotedStr, '\'') === 0) {
            $quotedStr = substr($quotedStr, 1);
        }

        if (strpos($quotedStr, '"') === (strlen($quotedStr) - 1)) {
            $quotedStr = substr($quotedStr, 0, -1);
        } elseif (strpos($quotedStr, '\'') === (strlen($quotedStr) - 1)) {
            $quotedStr = substr($quotedStr, 0, -1);
        }

        return $quotedStr;
    }
}
