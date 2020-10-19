<?php

namespace App\components\storage\tikv;

class Codec
{
    /**
     * @param string $rawKey
     * @return string
     */
    public static function escapeKey(string $rawKey): string
    {
        return str_replace(':', '\\:', $rawKey);
    }

    /**
     * @param string $escapedKey
     * @return string
     */
    public static function unescapeKey(string $escapedKey): string
    {
        return str_replace('\\:', ':', $escapedKey);
    }
}
