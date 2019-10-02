<?php

namespace Core\Hook;

use Core\Log\LogException;

class Hook
{

    protected static $storages;

    /**
     * @param string $name
     * @param callable $callable
     * @param int $priority
     */
    public static function add(string $name, callable $callable, int $priority = 50)
    {
        self::$storages[$name][] = [
            'priority' => $priority,
            'callable' => $callable
        ];
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
     * @param string $name
     * @param null $args
     * @throws LogException
     */
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

    /**
     * @param $callable
     * @param $args
     * @return mixed
     */
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
