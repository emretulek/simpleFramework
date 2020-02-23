<?php


namespace Core\Exceptions;


use Core\App;
use Throwable;

/**
 * Class Exceptions
 * @package Core\Exceptions
 * @method static debug(Throwable $e, int $backtrace = 0)
 */
class Exceptions
{
    public static function __callStatic($name, $arguments)
    {
        $exceptionHandler = App::getInstance(ExceptionHandler::class);
        $exceptionHandler->$name($arguments[0], $arguments[1] ?? 0);
    }
}
