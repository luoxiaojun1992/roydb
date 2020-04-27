<?php

namespace App\components\storage;

class StorageFactory
{
    /**
     * @return AbstractStorage
     */
    public static function create()
    {
        $defaultStorageEngine = config('roydb.storage.default');
        $storageClass = config('roydb.storage.engines.' . $defaultStorageEngine . '.class');
        return new $storageClass;
    }
}
