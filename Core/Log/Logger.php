<?php

namespace Core\Log;

use Core\App;
use Core\Config\Config;

Class Logger
{

    /**
     * @return LogWriterInterface|bool
     */
    public static function init()
    {
        if (Config::get('app.log.enable')) {

            if(Config::get('app.log.driver') == 'database') {
                return App::getInstance(new DatabaseLogWriter());
            }

            return App::getInstance(new FileLogWriter());
        }

        return false;
    }

    /**
     * @param $message
     * @param $data
     * @param $type
     * @return bool
     */
    public static function writer($message, $data, $type)
    {
        if ($logger = self::init()) {
            return $logger->writer($message, $data, $type);
        }

        return false;
    }

    /**
     * @param $message
     * @param null $data
     * @return bool
     */
    public static function debug($message, $data = null)
    {
        return self::writer($message, $data, 'DEBUG');
    }

    /**
     * @param $message
     * @param null $data
     * @return bool
     */
    public static function info($message, $data = null)
    {
        return self::writer($message, $data, 'INFO');
    }

    /**
     * @param $message
     * @param null $data
     * @return bool
     */
    public static function notice($message, $data = null)
    {
        return self::writer($message, $data, 'NOTICE');
    }

    /**
     * @param $message
     * @param null $data
     * @return bool
     */
    public static function warning($message, $data = null)
    {
        return self::writer($message, $data, 'WARNING');
    }

    /**
     * @param $message
     * @param null $data
     * @return bool
     */
    public static function error($message, $data = null)
    {
        return self::writer($message, $data, 'ERROR');
    }

    /**
     * @param $message
     * @param null $data
     * @return bool
     */
    public static function critical($message, $data = null)
    {
        return self::writer($message, $data, 'CRITICAL');
    }

    /**
     * @param $message
     * @param null $data
     * @return bool
     */
    public static function emergency($message, $data = null)
    {
        return self::writer($message, $data, 'EMERGENCY');
    }
}
