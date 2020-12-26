<?php
return  array(
    /**
     * Site base path default "/"
     */
    'path' => '/',

    /**
     * App name
     */
    'app_name' => 'Simple Framework',

    /**
     * {app_key} projeye özgü anahtar şifreleme türüne göre ayarlanmalı mevcut key 256bit
     */
    'app_key' => 'eThWmZq4t6w9z$C&F)J@NcRfUjXn2r5u',

    /**
     * {debug} -1 tüm hatalar dosyaya yazılır ekrana ve consola çıktı verilmez.
     * {debug} 0 Fatal error tüm çıktıyı engeller. Ekrana ve consola çıktı verilmez. error_log notice hariç hataları kaydeder. (Tavsiye edilen)
     * {debug} 1 Notice hariç tüm hatalar javascript konsoluna ve error_loga yazılır. Gizli debug.
     * {debug} 2 tüm hatalar ekrana basılır ve error_loga yazılır. Geliştirici modu.
     * {debug} 3 debug_back_trace aktif olur. Geliştirici modu.
     */
    'debug' => 2,

    /**
     * {sql_driver} database.php ayarlarında kullanılacak driver
     */
    'sql_driver' => 'mysql',

    /**
     * Default language
     */
    'language' => [
        'key' => 'tr',
        'name' => 'Türkçe',
        'locale' => 'TR-tr'
    ],
    /**
     * Charset
     */
    'charset' => 'UTF-8',

    /**
     * Timezone
     */
    'timezone' => 'Europe/Istanbul',

    /**
     * Şifrelemede kullanılacak algoritma
     * AES-256-CBC or AES-128-CBC
     */
    'encrypt_algo' => 'AES-256-CBC',

    /**
     * "md5", "sha256", "haval160,4" ve benzerleri
     */
    'hash_algo' => 'sha256',

    /**
     * true php password_hash DEFUALT_HASH yöntemi kullanılır, aksi halde belirtilen hash yöntemi kullanılır
     * true, "md5", "sha256", "haval160,4" ve benzerleri
     */
    'password_hash' => 'md5',

    /**
     * driver [file or database]
     */
    'logger_driver' => 'file',

    /**
     * cache.php
     * driver seçeneklerinden biri
     * [file, apcu, database, redis, memcached]
     */
    'cache_driver' => 'null',
);
