<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: roydb.proto

namespace Roydb;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>roydb.CommitResponse</code>
 */
class CommitResponse extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>bool result = 1;</code>
     */
    private $result = false;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type bool $result
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Roydb::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>bool result = 1;</code>
     * @return bool
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Generated from protobuf field <code>bool result = 1;</code>
     * @param bool $var
     * @return $this
     */
    public function setResult($var)
    {
        GPBUtil::checkBool($var);
        $this->result = $var;

        return $this;
    }

}

