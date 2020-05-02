<?php

namespace App\services\roydb;

/**
 */
interface StatInterface
{

    /**
     * @param \Roydb\EstIndexCardRequest $request
     * @return \Roydb\EstIndexCardResponse
     */
    public function EstIndexCard(\Roydb\EstIndexCardRequest $request);

}
