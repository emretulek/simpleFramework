<?php 
/**
 * @Created 14.12.2020 04:29:59
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class Cache
 * @package Core\Facades
 */


namespace Core\Facades;

/**
 * @see \Core\Cache\Cache::get()
 * @method static mixed get(string $key, $default = null)
 * --------------------------------------------------------------
 * @see \Core\Cache\Cache::set()
 * @method static bool set(string $key, $value, $ttl = null)
 * --------------------------------------------------------------
 * @see \Core\Cache\Cache::add()
 * @method static bool add(string $key, $value, $ttl = null)
 * ---------------------------------------------------------------
 * @see \Core\Cache\Cache::getSet()
 * @method static mixed getSet(string $key, $ttl = null, $default = null)
 * --------------------------------------------------------------
 * @see \Core\Cache\Cache::delete()
 * @method static bool delete(string $key)
 * --------------------------------------------------------------
 * @see \Core\Cache\Cache::clear()
 * @method static bool clear()
 * --------------------------------------------------------------
 * @see \Core\Cache\Cache::getMultiple()
 * @method static array getMultiple(array $keys)
 * --------------------------------------------------------------
 * @see \Core\Cache\Cache::setMultiple()
 * @method static bool setMultiple(array $items, $ttl = null)
 * ---------------------------------------------------------------
 * @see \Core\Cache\Cache::deleteMultiple()
 * @method static bool deleteMultiple(array $keys)
 * ---------------------------------------------------------------
 * @see \Core\Cache\Cache::has()
 * @method static bool has(string $key)
 * ---------------------------------------------------------------
 * @see \Core\Cache\Cache::increment()
 * @method static int increment(string $key, $value = 1)
 * ---------------------------------------------------------------
 * @see \Core\Cache\Cache::decrement()
 * @method static int decrement(string $key, $value = 1)
 * ---------------------------------------------------------------
 * @mixin \Core\Cache\Cache
 * @see \Core\Cache\Cache
 */

class Cache extends Facade {

    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \Core\Cache\Cache::class;
    }
}
