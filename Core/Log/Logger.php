<?php

namespace Core\Log;

use Core\App;

Class Logger
{

    private static $instance = false;

    /**
     * @param LogInterface|null logClass
     * @return bool|mixed
     */
    public static function init(?LogInterface $logClass)
    {
        if($logClass instanceof  LogInterface) {
            return self::$instance = App::getInstance($logClass);
        }
        return self::$instance;
    }


    /**
     * @param $message
     * @param $data
     * @param $type
     * @return bool
     */
    public static function writer($message, $data, $type)
    {
        if ($logger = self::init(null)) {
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
