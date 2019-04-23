<?php

define('ROOT', dirname(__DIR__).'/');
define('EXT', '.php');

mb_http_output("UTF-8");
mb_internal_encoding("UTF-8");
date_default_timezone_set('Europe/Istanbul');

require_once(ROOT . 'Core/helper.php');
require_once(ROOT . 'Core/Autoload/Autoload.php');

new Core\Autoload\AutoLoad();
