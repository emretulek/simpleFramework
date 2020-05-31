<?php

use Core\Config\Config;
use Core\Exceptions\ExceptionHandler;

define('ROOT', dirname(__DIR__).'/');
define('EXT', '.php');

require_once(ROOT . 'Core/helper.php');
require_once(ROOT . 'Core/Autoload/AutoLoad.php');
$autoLoad = new Core\Autoload\AutoLoad();

/**
 * Exception sınıfını başlat
 */
$exceptions = new ExceptionHandler();
$exceptions->bootstrap();


/**
 * başlangıç yerel ayarlar
 */
mb_http_output(Config::get('app.charset'));
mb_internal_encoding(Config::get('app.charset'));
date_default_timezone_set(Config::get('app.timezone'));

/**
 *  Uygulamayı başlat
 */
$autoLoad->loadAlias();
$autoLoad->loadFunctions();
$autoLoad->loadServices();
$autoLoad->loadRoutes();
