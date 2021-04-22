<?php
return [
    'driver' => 'file',

    //nullcache driver olarak atanırsa cache devre dışı bırakılmış olur
    'null' => [
        'driver' => 'null'
    ],

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
    'redis' => 'driver1',

    //memcached.php driver name
    'memcached' => 'driver1',
];
