<?php

namespace Core\Hook;

use Core\Log\LogException;

class Hook
{

    protected static $storages;

    public static function add(string $name, callable $callable, int $priority = 50)
    {
        self::$storages[$name][] = [
            'priority' => $priority,
            'callable' => $callable
        ];
    }

    public static function remove($name)
    {
        if (isset(self::$storages[$name])) {
            unset(self::$storages[$name]);
        }
    }

    public static function exec(string $name, $args = null)
    {
        if (isset(self::$storages[$name])) {

            asort(self::$storages[$name]);

            foreach (self::$storages[$name] as $storage) {
                self::call($storage['callable'], $args);
            }
        } else {

            throw new LogException("Hook name -> {$name} is not found");
        }
    }

    private static function call($callable, $args)
    {
        $args = is_array($args) ? $args : [$args];

        if (is_array($callable)) {

            if(is_callable($callable[0])) {
                return call_user_func_array([$callable[0], $callable[1]], $args);
            }else{
                return call_user_func_array([new $callable[0], $callable[1]], $args);
            }
        }

        return call_user_func_array($callable, $args);
    }
}
