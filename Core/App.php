<?php

namespace Core;


class App
{
    private static $instanceStorage = [];

    /**
     * Bir sınıfı method olarak çağırmayı dener
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return self::getInstance($name, $arguments);
    }

    /**
     * Bir sınıfın örneğini oluşturur, daha önce oluşturulmuşsa onu döndürür. Singletion
     *
     * @param $callable
     * @param array $args
     * @return mixed
     */
    public static function getInstance($callable, $args = [])
    {

        if (is_object($callable)) {
            $storageName = get_class($callable);
        }elseif(is_array($callable)){
            $storageName = $callable[0];
        }else{
            $storageName = $callable;
        }

        if (array_key_exists($storageName, self::$instanceStorage)) {
            return self::$instanceStorage[$storageName];
        }

        return self::$instanceStorage[$storageName] = self::caller($callable, $args);
    }

    /**
     * Sınıf, fonksiyon ve methodları çağırmaya zorlar
     *
     * @param $callable
     * @param array $args
     * @return mixed
     */
    public static function caller($callable, $args = [])
    {
        $args = is_array($args) ? $args : [$args];

        if (is_object($callable)) {
            return $callable;
        } elseif (is_array($callable)) {
            return call_user_func_array([new $callable[0], $callable[1]], $args);
        }else{
            if(class_exists($callable)){
                return new $callable(...$args);
            }else{
                return call_user_func_array($callable, $args);
            }
        }
    }
}
