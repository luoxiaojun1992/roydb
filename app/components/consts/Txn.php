<?php

namespace App\components\consts;

class Txn
{
    const STATUS_PENDING = 0;
    const STATUS_BEGIN = 1;
    const STATUS_COMMITTED = 2;
    const STATUS_ROLLBACK = 3;
}
