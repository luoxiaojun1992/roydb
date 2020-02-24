<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: roydb.proto

namespace Roydb;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>roydb.RowData</code>
 */
class RowData extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>repeated .roydb.Field field = 1;</code>
     */
    private $field;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Roydb\Field[]|\Google\Protobuf\Internal\RepeatedField $field
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Roydb::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>repeated .roydb.Field field = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Generated from protobuf field <code>repeated .roydb.Field field = 1;</code>
     * @param \Roydb\Field[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setField($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Roydb\Field::class);
        $this->field = $arr;

        return $this;
    }

}
