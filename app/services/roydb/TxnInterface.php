<?php

namespace App\services\roydb;

/**
 */
interface TxnInterface
{

    /**
     * @param \Roydb\BeginRequest $request
     * @return \Roydb\BeginResponse
     */
    public function Begin(\Roydb\BeginRequest $request);
    /**
     * @param \Roydb\CommitRequest $request
     * @return \Roydb\CommitResponse
     */
    public function Commit(\Roydb\CommitRequest $request);
    /**
     * @param \Roydb\RollbackRequest $request
     * @return \Roydb\RollbackResponse
     */
    public function Rollback(\Roydb\RollbackRequest $request);

}
