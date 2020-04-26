<?php

namespace App\components\plans;

use App\components\Ast;
use App\components\storage\AbstractStorage;

interface PlanInterface
{
    public function execute();

    public function __construct(Ast $ast, AbstractStorage $storage, int $txnId = 0);
}
