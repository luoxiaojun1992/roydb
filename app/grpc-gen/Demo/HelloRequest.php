<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: demo.proto

namespace Demo;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * The request message containing the user's name.
 *
 * Generated from protobuf message <code>demo.HelloRequest</code>
 */
class HelloRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>string name = 1;</code>
     */
    protected $name = '';
    /**
     * Generated from protobuf field <code>string addr = 2;</code>
     */
    protected $addr = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $name
     *     @type string $addr
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Demo::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>string name = 1;</code>
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Generated from protobuf field <code>string name = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setName($var)
    {
        GPBUtil::checkString($var, True);
        $this->name = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string addr = 2;</code>
     * @return string
     */
    public function getAddr()
    {
        return $this->addr;
    }

    /**
     * Generated from protobuf field <code>string addr = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setAddr($var)
    {
        GPBUtil::checkString($var, True);
        $this->addr = $var;

        return $this;
    }

}

