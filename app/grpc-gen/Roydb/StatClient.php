<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Roydb;

/**
 */
class StatClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts = []) {
        parent::__construct($hostname, $opts);
    }

    /**
     * @param \Roydb\EstIndexCardRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Roydb\EstIndexCardResponse[]|\Roydb\EstIndexCardResponse|\Grpc\StringifyAble[]
     */
    public function EstIndexCard(\Roydb\EstIndexCardRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/roydb.Stat/EstIndexCard',
        $argument,
        ['\Roydb\EstIndexCardResponse', 'decode'],
        $metadata, $options);
    }

}
