<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: roydb.proto

namespace Roydb;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>roydb.EstIndexCardRequest</code>
 */
class EstIndexCardRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>string schema = 1;</code>
     */
    private $schema = '';
    /**
     * Generated from protobuf field <code>string index = 2;</code>
     */
    private $index = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $schema
     *     @type string $index
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Roydb::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>string schema = 1;</code>
     * @return string
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Generated from protobuf field <code>string schema = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setSchema($var)
    {
        GPBUtil::checkString($var, True);
        $this->schema = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string index = 2;</code>
     * @return string
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Generated from protobuf field <code>string index = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setIndex($var)
    {
        GPBUtil::checkString($var, True);
        $this->index = $var;

        return $this;
    }

}

