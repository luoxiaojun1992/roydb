<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: roydb.proto

namespace Roydb;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>roydb.RollbackRequest</code>
 */
class RollbackRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>int64 ts = 1;</code>
     */
    private $ts = 0;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int|string $ts
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Roydb::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>int64 ts = 1;</code>
     * @return int|string
     */
    public function getTs()
    {
        return $this->ts;
    }

    /**
     * Generated from protobuf field <code>int64 ts = 1;</code>
     * @param int|string $var
     * @return $this
     */
    public function setTs($var)
    {
        GPBUtil::checkInt64($var);
        $this->ts = $var;

        return $this;
    }

}

