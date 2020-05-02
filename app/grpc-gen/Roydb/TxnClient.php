<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Roydb;

/**
 */
class TxnClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts = []) {
        parent::__construct($hostname, $opts);
    }

    /**
     * @param \Roydb\BeginRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Roydb\BeginResponse[]|\Roydb\BeginResponse|\Grpc\StringifyAble[]
     */
    public function Begin(\Roydb\BeginRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/roydb.Txn/Begin',
        $argument,
        ['\Roydb\BeginResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Roydb\CommitRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Roydb\CommitResponse[]|\Roydb\CommitResponse|\Grpc\StringifyAble[]
     */
    public function Commit(\Roydb\CommitRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/roydb.Txn/Commit',
        $argument,
        ['\Roydb\CommitResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Roydb\RollbackRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Roydb\RollbackResponse[]|\Roydb\RollbackResponse|\Grpc\StringifyAble[]
     */
    public function Rollback(\Roydb\RollbackRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/roydb.Txn/Rollback',
        $argument,
        ['\Roydb\RollbackResponse', 'decode'],
        $metadata, $options);
    }

}
