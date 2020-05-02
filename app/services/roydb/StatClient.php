<?php

namespace App\services\roydb;

/**
 *
 * @mixin \Roydb\StatClient
 */
class StatClient  extends \Grpc\ClientStub
{

    protected $grpc_client = \Roydb\StatClient::class;

    protected $endpoint = '127.0.0.1:50051';

}
