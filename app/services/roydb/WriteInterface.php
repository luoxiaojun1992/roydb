<?php

namespace App\services\roydb;

/**
 */
interface WriteInterface
{

    /**
     * @param \Roydb\InsertRequest $request
     * @return \Roydb\InsertResponse
     */
    public function Insert(\Roydb\InsertRequest $request);
    /**
     * @param \Roydb\DeleteRequest $request
     * @return \Roydb\DeleteResponse
     */
    public function Delete(\Roydb\DeleteRequest $request);
    /**
     * @param \Roydb\UpdateRequest $request
     * @return \Roydb\UpdateResponse
     */
    public function Update(\Roydb\UpdateRequest $request);
    /**
     * @param \Roydb\CreateRequest $request
     * @return \Roydb\CreateResponse
     */
    public function Create(\Roydb\CreateRequest $request);

}
