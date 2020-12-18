<?php
/**
 * Veritabanı sınıfında kullanılacak sql bağlantıları.
 * Bir projede birden fazla sql driver kullanılabilir.
 */
return [
    'mysql' => [
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'port'      => '3306',
        'database'  => 'phpfw',
        'user'      => 'root',
        'password'  => '',
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_general_ci',
        'options' => [
            [
                PDO::ATTR_DEFAULT_FETCH_MODE,
                PDO::FETCH_OBJ
            ]
        ]
    ],
    'mysql2' => [
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'port'      => '3306',
        'database'  => 'phpfw',
        'user'      => 'root',
        'password'  => '',
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_general_ci',
        'options' => [
            [
                PDO::ATTR_DEFAULT_FETCH_MODE,
                PDO::FETCH_OBJ
            ]
        ]
    ],
];
