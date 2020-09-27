<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Roydb;

/**
 */
class WriteClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts = []) {
        parent::__construct($hostname, $opts);
    }

    /**
     * @param \Roydb\InsertRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Roydb\InsertResponse[]|\Roydb\InsertResponse|\Grpc\StringifyAble[]
     */
    public function Insert(\Roydb\InsertRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/roydb.Write/Insert',
        $argument,
        ['\Roydb\InsertResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Roydb\DeleteRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Roydb\DeleteResponse[]|\Roydb\DeleteResponse|\Grpc\StringifyAble[]
     */
    public function Delete(\Roydb\DeleteRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/roydb.Write/Delete',
        $argument,
        ['\Roydb\DeleteResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Roydb\UpdateRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Roydb\UpdateResponse[]|\Roydb\UpdateResponse|\Grpc\StringifyAble[]
     */
    public function Update(\Roydb\UpdateRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/roydb.Write/Update',
        $argument,
        ['\Roydb\UpdateResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Roydb\CreateRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Roydb\CreateResponse[]|\Roydb\CreateResponse|\Grpc\StringifyAble[]
     */
    public function Create(\Roydb\CreateRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/roydb.Write/Create',
        $argument,
        ['\Roydb\CreateResponse', 'decode'],
        $metadata, $options);
    }

}
