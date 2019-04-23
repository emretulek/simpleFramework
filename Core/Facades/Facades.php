<?php
namespace Core\Facades;

Abstract Class Facades{

    protected static $instanceStorage = [];

    abstract static function getOriginalClassName();

    public static function __callStatic($method, $args)
    {
        return call_user_func_array([self::getInstance(), $method], $args);
    }

    public function __call($method, $args)
    {
        return call_user_func_array([self::getInstance(), $method], $args);
    }

    private static function getInstance()
    {
        $className = static::getOriginalClassName();

        if(in_array($className, self::$instanceStorage)){

            return self::$instanceStorage[$className];
        }

        return self::$instanceStorage[$className] = new $className;
    }
}
