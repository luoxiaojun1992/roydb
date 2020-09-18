<?php

namespace App\components\storage;

class StorageFactory
{
    /**
     * @param null|string $storageEngine
     * @return AbstractStorage
     */
    public static function create($storageEngine = null)
    {
        $storageEngine = $storageEngine ?? config('roydb.storage.default');
        $storageClass = config('roydb.storage.engines.' . $storageEngine . '.class');
        return new $storageClass;
    }
}
