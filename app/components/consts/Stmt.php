<?php

namespace App\components\consts;

class Stmt
{
    const TYPE_SELECT = 'select';
    const TYPE_INSERT = 'insert';
    const TYPE_DELETE = 'delete';
    const TYPE_UPDATE = 'update';
    const TYPE_BEGIN = 'begin';
    const TYPE_COMMIT = 'commit';
}
