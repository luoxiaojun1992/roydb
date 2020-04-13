<?php

return [
    'storage' => [
        'default' => 'tikv',
        'engines' => [
            'tikv' => [
                'class' => \App\components\storage\tikv\TiKV::class,
            ]
        ]
    ]
];
