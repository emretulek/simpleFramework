<?php 
/**
 * @Created 08.12.2020 18:34:22
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class Cookie
 * @package Core\Facades
 */


namespace Core\Facades;

/**
 * @see \Core\Cookie\Cookie::set()
 * @method static bool set(string $name, string $value, $lifetime = "+1 Day", $path = "/", $domain = null, $secure = null, $http_only = true, $sameSite = 'strict')
 * -----------------------------------------------------------------------
 * @see \Core\Cookie\Cookie::set()
 * @method static mixed get(string $name = null)
 * -----------------------------------------------------------------------
 * @see \Core\Cookie\Cookie::remove()
 * @method static bool remove(string $name)
 * -----------------------------------------------------------------------
 * @see \Core\Cookie\Cookie::destroy()
 * @method static bool destroy()
 * ----------------------------------------------------------------------
 * @mixin \Core\Cookie\Cookie
 * @see \Core\Cookie\Cookie
 */

class Cookie extends Facade {

    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \Core\Cookie\Cookie::class;
    }
}
