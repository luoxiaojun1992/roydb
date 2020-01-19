<?php

namespace App\services\roydb;

/**
 *
 * @mixin \Roydb\QueryClient
 */
class QueryClient  extends \Grpc\ClientStub
{

    protected $grpc_client = \Roydb\QueryClient::class;

    protected $endpoint = '127.0.0.1:50051';

}
