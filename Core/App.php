<?php

namespace Core;


use Closure;
use Exceptions;
use InvalidArgumentException;

class App
{
    private static array $instanceStorage = [];

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
        $storageName = self::getInstanceName($callable);

        if ($storageName && array_key_exists($storageName, self::$instanceStorage)) {
            return self::$instanceStorage[$storageName];
        }

        try{
            return self::$instanceStorage[$storageName] = self::caller($callable, $args);
        }catch (InvalidArgumentException $e){
            Exceptions::debug($e);
        }

        return null;
    }

    /**
     * Sınıf, fonksiyon ve methodları çağırmaya zorlar
     * @example
     * caller("funcName", [$arg1, $arg2])
     * caller(["className"], [$arg1, $arg2])
     * caller(["className", "methodName"], [$arg1, $arg2])
     * @param $callable string|array|object
     * @param array $args
     * @return mixed
     */
    public static function caller($callable, array $args = [])
    {
        if($callable instanceof Closure){
            return call_user_func_array($callable, $args);
        }

        if(is_array($callable) && is_callable($callable)){
            return call_user_func_array([new $callable[0], $callable[1]], $args);
        }

        if(is_string($callable)){

            if(is_callable($callable)){
                return call_user_func_array($callable, $args);
            }

            if(class_exists($callable)){
                return new $callable(...$args);
            }
        }

        throw new InvalidArgumentException('Invalid argument for callable', E_ERROR);
    }


    /**
     * @param $callable
     * @return callable|false|mixed|string|null
     */
    public static function getInstanceName($callable)
    {
        if($callable instanceof Closure){
            return spl_object_id($callable);
        }

        if(is_array($callable) && is_callable($callable)){
            return $callable[0];
        }

        if(is_string($callable)){

            if(is_callable($callable)){
                return $callable;
            }

            if(class_exists($callable)){
                return $callable;
            }
        }

        return null;
    }
}
