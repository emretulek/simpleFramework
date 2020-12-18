<?php 
/**
 * @Created 08.12.2020 15:15:46
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class Session
 * @package Core\Facades
 */


namespace Core\Facades;

/**
 * @see \Core\Session\Session::start()
 * @method static start()
 * --------------------------------------------------------------------
 * @see \Core\Session\Session::status()
 * @method static bool status()
 * --------------------------------------------------------------------
 * @see \Core\Session\Session::set()
 * @method static bool set(string $key, mixed $value)
 * ---------------------------------------------------------------------
 * @see \Core\Session\Session::get()
 * @method static mixed get(string $key)
 * ---------------------------------------------------------------------
 * @see \Core\Session\Session::remove()
 * @method static bool remove(string $key)
 * ---------------------------------------------------------------------
 * @see \Core\Session\Session::tempSet()
 * @method static bool tempSet(string $name, $value, int $lifecycle = 1)
 * ---------------------------------------------------------------------
 * @see \Core\Session\Session::tempGet()
 * @method static mixed tempGet(string $name)
 * ---------------------------------------------------------------------
 * @see \Core\Session\Session::tempClear()
 * @method static void tempClear()
 * ---------------------------------------------------------------------
 * @see \Core\Session\Session::destroy()
 * @method static bool destroy()
 * ----------------------------------------------------------------------
 * @mixin \Core\Session\Session
 * @see \Core\Session\Session
 */
class Session extends Facade {

    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \Core\Session\Session::class;
    }
}
