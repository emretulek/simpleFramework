<?php
return [
    //php apc
    'apcu' => [
        'driver' => 'apcu'
    ],

    //dosya sistemi
    'file' => [
        'driver' => 'file',
        'path' => ROOT . '/storage/cache'
    ],

    //veritabanı
    'database' => [
        'driver' => 'database',
        'table' => 'cache'
    ],

    //redis
    'redis' => [
        'driver' => 'redis',
        'server' => '127.0.0.1',
        'port' => 6379,
        'options' => [
            'database' => 0,
            'auth' => [
                'pass' => ''
            ],
            'prefis' => '',
            'read_timeout' => 0,
            'scan' => '',
            'name' => 'spfw'
        ]
    ],

    //memcached
    'memcached' => [
        'driver' => 'memcached',
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

    //nullcache driver olarak atanırsa cache devre dışı bırakılmış olur
    'null' => [
        'driver' => 'null'
    ]
];
