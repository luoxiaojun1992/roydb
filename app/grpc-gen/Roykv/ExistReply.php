<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: roykv.proto

namespace Roykv;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>roykv.ExistReply</code>
 */
class ExistReply extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>bool existed = 1;</code>
     */
    protected $existed = false;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type bool $existed
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Roykv::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>bool existed = 1;</code>
     * @return bool
     */
    public function getExisted()
    {
        return $this->existed;
    }

    /**
     * Generated from protobuf field <code>bool existed = 1;</code>
     * @param bool $var
     * @return $this
     */
    public function setExisted($var)
    {
        GPBUtil::checkBool($var);
        $this->existed = $var;

        return $this;
    }

}

