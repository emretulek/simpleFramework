<?php

use Core\Config\Config;
use Core\Exceptions\ExceptionHandler;

define('ROOT', dirname(__DIR__).'/');
define('EXT', '.php');

require_once(ROOT . 'Core/helper.php');
require_once(ROOT . 'Core/Autoload/Autoload.php');
$autoLoad = new Core\Autoload\AutoLoad();
$autoLoad->loadAlias();

/**
 * Exception sınıfını başlat
 */
$exceptions = new ExceptionHandler();
$exceptions->bootstrap();


/**
 * Cache
 */
if (Config::get('app.cache.enable')) {

    if(Config::get('app.cache.driver') == 'memcache') {
        Core\Cache\Cache::init(new Core\Cache\MemoryCache());
    }elseif(Config::get('app.cache.driver') == 'database'){
        Core\Cache\Cache::init(new Core\Cache\DatabaseCache());
    }else{
        Core\Cache\Cache::init(new Core\Cache\FileCache());
    }
}

/**
 * Log
 */
if (Config::get('app.log.enable')) {

    if(Config::get('app.log.driver') == 'database') {
        Core\Log\Logger::init(new Core\Log\DatabaseLog());
    }else{
        Core\Log\Logger::init(new Core\Log\FileLog());
    }
}

/**
 * başlangıç yerel ayarlar
 */
mb_http_output(Config::get('app.charset'));
mb_internal_encoding(Config::get('app.charset'));
date_default_timezone_set(Config::get('app.timezone'));


/**
 *  Uygulamayı başlat
 */

$autoLoad->loadFunctions();
$autoLoad->loadServices();
$autoLoad->loadRoutes();
