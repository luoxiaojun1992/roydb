<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: roydb.proto

namespace Roydb;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>roydb.EstIndexCardResponse</code>
 */
class EstIndexCardResponse extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>bool accepted = 1;</code>
     */
    private $accepted = false;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type bool $accepted
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Roydb::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>bool accepted = 1;</code>
     * @return bool
     */
    public function getAccepted()
    {
        return $this->accepted;
    }

    /**
     * Generated from protobuf field <code>bool accepted = 1;</code>
     * @param bool $var
     * @return $this
     */
    public function setAccepted($var)
    {
        GPBUtil::checkBool($var);
        $this->accepted = $var;

        return $this;
    }

}

