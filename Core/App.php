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
     * @param $class
     * @param array $args
     * @return mixed
     */
    public static function getInstance($class, $args = [])
    {
        $class = is_object($class) ? get_class($class) : $class;

        if(isset(self::$instanceStorage[$class])){
            return self::$instanceStorage[$class];
        }
        return self::$instanceStorage[$class] = self::caller($class, $args);
    }

    /**
     * Sınıf, fonksiyon ve methodları çağırmaya zorlar
     *
     * @param $class
     * @param array $args
     * @return mixed
     */
    public static function caller($class, $args = [])
    {
        $args = is_array($args) ? $args : [$args];

        if(is_string($class)){
            return new $class(...$args);
        }elseif(is_object($class)){
            return call_user_func_array($class, $args);
        }else{
            return call_user_func_array([new $class[0], $class[1]], $args);
        }
    }
}
