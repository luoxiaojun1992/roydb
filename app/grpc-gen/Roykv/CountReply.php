<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: roykv.proto

namespace Roykv;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>roykv.CountReply</code>
 */
class CountReply extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>uint64 count = 1;</code>
     */
    protected $count = 0;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int|string $count
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Roykv::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>uint64 count = 1;</code>
     * @return int|string
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Generated from protobuf field <code>uint64 count = 1;</code>
     * @param int|string $var
     * @return $this
     */
    public function setCount($var)
    {
        GPBUtil::checkUint64($var);
        $this->count = $var;

        return $this;
    }

}

