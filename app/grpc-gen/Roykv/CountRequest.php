<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: roykv.proto

namespace Roykv;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>roykv.CountRequest</code>
 */
class CountRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>string startKey = 1;</code>
     */
    private $startKey = '';
    /**
     * Generated from protobuf field <code>string endKey = 2;</code>
     */
    private $endKey = '';
    /**
     * Generated from protobuf field <code>string keyPrefix = 3;</code>
     */
    private $keyPrefix = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $startKey
     *     @type string $endKey
     *     @type string $keyPrefix
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Roykv::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>string startKey = 1;</code>
     * @return string
     */
    public function getStartKey()
    {
        return $this->startKey;
    }

    /**
     * Generated from protobuf field <code>string startKey = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setStartKey($var)
    {
        GPBUtil::checkString($var, True);
        $this->startKey = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string endKey = 2;</code>
     * @return string
     */
    public function getEndKey()
    {
        return $this->endKey;
    }

    /**
     * Generated from protobuf field <code>string endKey = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setEndKey($var)
    {
        GPBUtil::checkString($var, True);
        $this->endKey = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string keyPrefix = 3;</code>
     * @return string
     */
    public function getKeyPrefix()
    {
        return $this->keyPrefix;
    }

    /**
     * Generated from protobuf field <code>string keyPrefix = 3;</code>
     * @param string $var
     * @return $this
     */
    public function setKeyPrefix($var)
    {
        GPBUtil::checkString($var, True);
        $this->keyPrefix = $var;

        return $this;
    }

}

