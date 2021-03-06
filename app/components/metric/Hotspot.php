<?php

namespace App\components\metric;

use App\components\storage\AbstractStorage;

class Hotspot
{
    /** @var AbstractStorage */
    protected $storage;

    public static function create(AbstractStorage $storage)
    {
        return new static($storage);
    }

    public function __construct(AbstractStorage $storage)
    {
        $this->storage = $storage;
    }

    //todo
}
