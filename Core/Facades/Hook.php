<?php

namespace Core\Facades;

/**
 * Class Hook
 * @package Core\Facades
 * -------------------------------------------------------------------------------
 * @see \Core\Hook\Hook::add()
 * @method static bool add(string $name, callable $callable, int $priority = 50)
 * -------------------------------------------------------------------------------
 * @see \Core\Hook\Hook::remove()
 * @method static void remove($name)
 * -------------------------------------------------------------------------------
 * @see \Core\Hook\Hook::exists()
 * @method static bool exists($name)
 * --------------------------------------------------------------------------------
 * @see \Core\Hook\Hook::list()
 * @method static array list()
 * --------------------------------------------------------------------------------
 * @see \Core\Hook\Hook::exec()
 * @method static mixed exec(string $name, $output)
 * --------------------------------------------------------------------------------
 * @see \Core\Hook\Hook
 */
class Hook extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \Core\Hook\Hook::class;
    }
}
