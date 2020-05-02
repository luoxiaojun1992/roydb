<?php

namespace App\services\roydb;

/**
 */
class StatService extends \SwFwLess\services\GrpcUnaryService implements StatInterface
{

    /**
     * @param \Roydb\EstIndexCardRequest $request
     * @return \Roydb\EstIndexCardResponse
     */
    public function EstIndexCard(\Roydb\EstIndexCardRequest $request)
    {
        //todo implements interface
    }

}
