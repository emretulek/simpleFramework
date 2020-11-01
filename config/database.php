<?php
/**
 * Veritabanı sınıfında kullanılacak sql bağlantıları.
 * Bir projede birden fazla sql driver kullanılabilir.
 */
return [
    'mysql' => [
        'driver'    => 'mysql',
        'dsn'      =>  'host=localhost;dbname=phpfw',
        'user'      =>  'root',
        'password'  =>  '',
        'charset'   =>  'utf8mb4',
        'collaction' =>  'utf8mb4_general_ci'
    ],
    'mysql2' => [
        'driver'    => 'mysql',
        'dsn'      =>  'host=localhost;dbname=test',
        'user'      =>  'root',
        'password'  =>  '',
        'charset'   =>  'utf8mb4',
        'collaction' =>  'utf8mb4_general_ci'
    ]
];
