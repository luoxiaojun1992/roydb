<?php

namespace App\components\storage;

class StorageBuilder
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
