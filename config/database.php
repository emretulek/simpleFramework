<?php
/**
 * Veritabanı sınıfında kullanılacak sql bağlantıları.
 * Bir projede birden fazla sql driver kullanılabilir.
 */
return array(
    'mysql' => array(
        'dsn'      =>  'mysql:host=localhost:3306;dbname=phpfw',
        'user'      =>  'root',
        'password'  =>  '',
        'charset'   =>  'utf8mb4',
        'collaction' =>  'utf8mb4_general_ci'
    )
);
