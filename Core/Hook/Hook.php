<?php

namespace Core\Hook;

use Core\App;

class Hook
{

    protected static array $storages = [];

    /**
     * @param string $name
     * @param callable $callable
     * @param int $priority
     */
    public static function add(string $name, callable $callable, int $priority = 50)
    {
        if(isset(self::$storages[$name][$priority])) {
            self::$storages[$name][] = $callable;
        }else{
            self::$storages[$name][$priority] = $callable;
        }

        ksort(self::$storages[$name]);
    }

    /**
     * @param $name
     */
    public static function remove($name)
    {
        if (isset(self::$storages[$name])) {
            unset(self::$storages[$name]);
        }
    }


    /**
     * @param $name
     * @return bool
     */
    public static function exists($name)
    {
        return isset(self::$storages[$name]);
    }

    /**
     * @return mixed
     */
    public static function list()
    {
        return self::$storages;
    }

    /**
     * @param string $name
     * @param mixed $output
     * @return mixed|null
     */
    public static function exec(string $name, $output)
    {
        if (self::exists($name)) {

            foreach (self::$storages[$name] as $callable) {
                $output = App::caller($callable,  [$output]);
            }
        }

        return $output;
    }
}
