<?php

namespace App\services\roydb;

/**
 *
 * @mixin \Roydb\TxnClient
 */
class TxnClient  extends \Grpc\ClientStub
{

    protected $grpc_client = \Roydb\TxnClient::class;

    protected $endpoint = '127.0.0.1:50051';

}
