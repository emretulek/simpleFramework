<?php
/**
 * {app_key} projeye özgü anahtar bir çeşit hash key.
 *
 * {debug} -1 error_log dahil tüm hata ve uyarıları kapatır.
 * {debug} 0 error_log hariç tüm hata ve uyarıları kapatır (tavsiye edilen).
 * {debug} 1 hatalar javascript konsoluna yazılır. Gizli debug.
 * {debug} 2 tüm hatalar ekrana basılır. Geliştirici modu.
 * {debug} 3 debug_back_trace aktif olur. Geliştirici modu.
 *
 * {auto_root} adres satırından routing işlemi otomatik yapılır
 *
 * {sql_driver} database.php ayarlarında kullanılacak driver
 *
 * {auth} Core\Auth sınıfını otomatik başlatır.
 */
return  array(
    /**
     * {app_key} projeye özgü anahtar bir çeşit hash key.
     */
    'app_key' => "a636DV",

    /**
     * {debug} -1 error_log dahil tüm hata ve uyarıları kapatır.
     * {debug} 0 error_log hariç tüm hata ve uyarıları kapatır (tavsiye edilen).
     * {debug} 1 hatalar javascript konsoluna yazılır. Gizli debug.
     * {debug} 2 tüm hatalar ekrana basılır. Geliştirici modu.
     * {debug} 3 debug_back_trace aktif olur. Geliştirici modu.
     */
    'debug' => 2,

    /**
     * enable [true or false]
     * driver [file or database]
     */
    'log' => [
        'enable' => true,
        'driver' => 'file'
    ],

    /**
     * Cache ayarları
     * enable [true or false]
     * driver [file or memcache]
     * memcache [host, port]
     */
    'cache' => [
        'enable' => true,
        'driver' => 'file',
        'memcache' => [
            'host' => 'localhost',
            'port' => 11211
        ]
    ],

    /**
     * {sql_driver} database.php ayarlarında kullanılacak driver
     */
    'sql_driver' => 'mysql',

    /**
     * Default language
     */
    'language' => 'tr'
);
