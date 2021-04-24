<?php
return [
    'driver' => null,
    'prefix' => false, //prefix true ise [options => name] ön ek olarak kullanılır
    'cookie_params' => [
        'lifetime' => 0,
        'path' => '/',
        'domain' => null,
        'secure' => null, //[true, false, null] null => https aktif ise true değilse false ayarlanır
        'httponly' => true,
        'samesite' => 'Strict' //[Strict, Lax, None]
    ],
    'options' => [
        //session_start($options) https://www.php.net/manual/tr/function.session-start.php
        'gc_probability' => 1,
        'gc_divisor' => 100,
        'gc_maxlifetime' => 36000,
        'save_path' => 'C:/laragon/tmp',
        #'name' => 'PHPSESSID'
    ],
    'file' => [
        'path' => ROOT.'/storage/session',
    ],
    'database' => [
        'table' => 'sessions'
    ],
    'memcached' => 'driver1', //memcached.php dosyasından sürücü adı
    'redis' => 'driver1', //redis.php dosyasından sürücü adı
];
