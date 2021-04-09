<?php
use Core\App;

define('EXT', '.php');
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__DIR__));

if(is_file(ROOT. '/vendor/autoload.php')) {
    require_once(ROOT . '/vendor/autoload.php');
}else{
    require_once(ROOT . '/Core/Autoload/autoload.php');
}

$app = new App(ROOT);

/**
 * başlangıç yerel ayarlar
 */
mb_http_output($app->config['app']['charset']);
mb_internal_encoding($app->config['app']['charset']);
date_default_timezone_set($app->config['app']['timezone']);

/**
 * Uygulamayı başlat
 */
$app->boot();
