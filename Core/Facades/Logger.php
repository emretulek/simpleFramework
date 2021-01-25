<?php

namespace Core\Facades;

/**
 * @see \Core\Log\Logger::emergency()
 * @method static void emergency(string $message, $context = array())
 * ---------------------------------------------------------------------------
 * @see \Core\Log\Logger::alert()
 * @method static void alert(string $message, array $context = array())
 * ---------------------------------------------------------------------------
 * @see \Core\Log\Logger::critical()
 * @method static void critical(string $message, array $context = array())
 * ---------------------------------------------------------------------------
 * @see \Core\Log\Logger::error()
 * @method static void error(string $message, array $context = array())
 * ---------------------------------------------------------------------------
 * @see \Core\Log\Logger::warning()
 * @method static void warning(string $message, array $context = array())
 * ---------------------------------------------------------------------------
 * @see \Core\Log\Logger::notice()
 * @method static void notice(string $message, array $context = array())
 * ---------------------------------------------------------------------------
 * @see \Core\Log\Logger::info()
 * @method static void info(string $message, array $context = array())
 * ---------------------------------------------------------------------------
 * @see \Core\Log\Logger::debug()
 * @method static void debug(string $message, array $context = array())
 * ---------------------------------------------------------------------------
 * @see \Core\Log\Logger::log()
 * @method static void log($level, string $message, array $context = array())
 * ---------------------------------------------------------------------------
 * @mixin \Core\Log\Logger
 * @see \Core\Log\Logger
 */
class Logger extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \Core\Log\Logger::class;
    }
}
