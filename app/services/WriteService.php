<?php

namespace App\services;

use App\services\roydb\WriteClient;
use Roydb\CreateRequest;
use Roydb\DeleteRequest;
use Roydb\InsertRequest;
use Roydb\UpdateRequest;
use SwFwLess\services\BaseService;

class WriteService extends BaseService
{
    /**
     * @return array
     */
    public function insert()
    {
        $start = microtime(true);
        $sql = $this->request->post('sql');
        $insertResponse = (new WriteClient())->Insert(
            (new InsertRequest())->setSql($sql)
        );

        if (!$insertResponse) {
            return [
                'code' => -1,
                'msg' => 'failed',
                'data' => [],
            ];
        }

        return [
            'code' => 0,
            'msg' => 'ok',
            'data' => [
                'affected_rows' => $insertResponse->getAffectedRows(),
                'time_usage' => microtime(true) - $start,
            ],
        ];
    }

    /**
     * @return array
     */
    public function update()
    {
        $start = microtime(true);
        $sql = $this->request->post('sql');
        $updateResponse = (new WriteClient())->Update(
            (new UpdateRequest())->setSql($sql)
        );

        if (!$updateResponse) {
            return [
                'code' => -1,
                'msg' => 'failed',
                'data' => [],
            ];
        }

        return [
            'code' => 0,
            'msg' => 'ok',
            'data' => [
                'affected_rows' => $updateResponse->getAffectedRows(),
                'time_usage' => microtime(true) - $start,
            ],
        ];
    }

    /**
     * @return array
     */
    public function delete()
    {
        $start = microtime(true);
        $sql = $this->request->post('sql');
        $deleteResponse = (new WriteClient())->Delete(
            (new DeleteRequest())->setSql($sql)
        );

        if (!$deleteResponse) {
            return [
                'code' => -1,
                'msg' => 'failed',
                'data' => [],
            ];
        }

        return [
            'code' => 0,
            'msg' => 'ok',
            'data' => [
                'affected_rows' => $deleteResponse->getAffectedRows(),
                'time_usage' => microtime(true) - $start,
            ],
        ];
    }

    public function create()
    {
        $start = microtime(true);
        $sql = $this->request->post('sql');
        $createResponse = (new WriteClient())->Create(
            (new CreateRequest())->setSql($sql)
        );

        if (!$createResponse) {
            return [
                'code' => -1,
                'msg' => 'failed',
                'data' => [],
            ];
        }

        return [
            'code' => 0,
            'msg' => 'ok',
            'data' => [
                'affected_rows' => $createResponse->getResult(),
                'time_usage' => microtime(true) - $start,
            ],
        ];
    }
}
