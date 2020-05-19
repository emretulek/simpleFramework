<?php
/**
 * Veritabanı sınıfında kullanılacak sql bağlantıları.
 * Bir projede birden fazla sql driver kullanılabilir.
 */
return [
    'mysql' => [
        'driver'    => 'mysql',
        'host'      =>  'localhost',
        'database'  =>  'phpfw',
        'user'      =>  'root',
        'password'  =>  '',
        'charset'   =>  'utf8mb4',
        'collaction' =>  'utf8mb4_general_ci'
    ],
    'mysql2' => [
        'driver'    => 'mysql',
        'host'      =>  'localhost',
        'database'  =>  'test',
        'user'      =>  'root',
        'password'  =>  '',
        'charset'   =>  'utf8mb4',
        'collaction' =>  'utf8mb4_general_ci'
    ]
];
