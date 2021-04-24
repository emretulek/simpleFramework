<?php

namespace Core\Facades;

/**
 * @see \Core\Cookie\Cookie::set()
 * @method static bool set(string $name, string $value, $lifetime = 2592000, $path = "/", $domain = null, $secure = null, $http_only = true, $sameSite = 'strict')
 * -----------------------------------------------------------------------
 * @see \Core\Cookie\Cookie::get()
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
class Cookie extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \Core\Cookie\Cookie::class;
    }
}
