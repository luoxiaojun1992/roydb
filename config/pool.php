<?php

return [
    'switch' => envInt('POOL_SWITCH', 1),
    'objects' => [
        \SwFwLess\middlewares\Route::class => [
            'pool_size' => envInt('ROUTER_POOL_SIZE', 5),
        ],
        \SwFwLess\components\http\Request::class => [
            'pool_size' => envInt('HTTP_REQUEST_POOL_SIZE', 5),
        ],
        \App\services\WriteService::class => [
            'pool_size' => 5,
        ],
        \App\services\roydb\WriteService::class => [
            'pool_size' => 5,
        ],
    ],
];
