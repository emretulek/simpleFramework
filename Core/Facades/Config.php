<?php 


namespace Core\Facades;

/**
 * @see \Core\Config\Config::get()
 * @method static mixed get(string $key)
 * --------------------------------------------------------------
 * @see \Core\Config\Config::set()
 * @method static void set(string $key, mixed $value)
 * --------------------------------------------------------------
 * @see \Core\Config\Config::has()
 * @method static bool has(string $key)
 * -------------------------------------------------------------
 * @see \Core\Config\Config::all()
 * @method static array all()
 * ------------------------------------------------------------
 * @see \Core\Config\Config::remove()
 * @method static bool remove(string $key)
 * -------------------------------------------------------------
 * @mixin \Core\Config\Config
 * @see \Core\Config\Config
 */

class Config extends Facade {

    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \Core\Config\Config::class;
    }
}
