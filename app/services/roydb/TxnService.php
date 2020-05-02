<?php

namespace App\services\roydb;

/**
 */
class TxnService extends \SwFwLess\services\GrpcUnaryService implements TxnInterface
{

    /**
     * @param \Roydb\BeginRequest $request
     * @return \Roydb\BeginResponse
     */
    public function Begin(\Roydb\BeginRequest $request)
    {
        //todo implements interface
    }
    /**
     * @param \Roydb\CommitRequest $request
     * @return \Roydb\CommitResponse
     */
    public function Commit(\Roydb\CommitRequest $request)
    {
        //todo implements interface
    }
    /**
     * @param \Roydb\RollbackRequest $request
     * @return \Roydb\RollbackResponse
     */
    public function Rollback(\Roydb\RollbackRequest $request)
    {
        //todo implements interface
    }

}
