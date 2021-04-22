<?php
return [
    'driver1' => [
        'connection_id' => 'project',
        'sasl' => [
            'username' => '',
            'password' => ''
        ],
        'options' => [
            // Memcached::OPT_CONNECT_TIMEOUT => 2000,
        ],
        'servers' => [
            [
                'host' => '127.0.0.1',
                'port' => 11211,
                'weight' => 100
            ]
        ]
    ],
    'driver2' => [
        'connection_id' => 'project',
        'sasl' => [
            'username' => '',
            'password' => ''
        ],
        'options' => [
            // Memcached::OPT_CONNECT_TIMEOUT => 2000,
        ],
        'servers' => [
            [
                'host' => '127.0.0.1',
                'port' => 11211,
                'weight' => 100
            ]
        ]
    ]
];
