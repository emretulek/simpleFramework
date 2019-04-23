<?php


namespace Core\Cache;


use Core\App;
use Core\Config\Config;

class Cache
{
    /**
     * @return CacheInterface|bool
     */
    public static function init()
    {
        if (Config::get('app.cache.enable')) {

            if(Config::get('app.cache.driver') == 'memcache') {
                return App::getInstance(new MemoryCache());
            }

            return App::getInstance(new FileCache());
        }

        return false;
    }


    /**
     * @param $key
     * @param $value
     * @param bool $compress
     * @param int $expires
     * @return bool
     */
    public static function add($key, $value, $compress = false, $expires = 2592000)
    {
        if($cache = self::init()){
            return $cache->add($key, $value, $compress, $expires);
        }

        return false;
    }


    /**
     * @param $key
     * @param $value
     * @param bool $compress
     * @param int $expires
     * @return bool
     */
    public static function set($key, $value, $compress = false, $expires = 2592000)
    {
        if($cache = self::init()){
            return $cache->set($key, $value, $compress, $expires);
        }

        return false;
    }


    /**
     * @param $key
     * @return bool|mixed
     */
    public static function get($key)
    {
        if($cache = self::init()){
            return $cache->get($key);
        }

        return false;
    }


    /**
     * @param $key
     * @return bool
     */
    public static function delete($key)
    {
        if($cache = self::init()){
            return $cache->delete($key);
        }

        return false;
    }


    /**
     * @return bool
     */
    public static function flush()
    {
        if($cache = self::init()){
            return $cache->flush();
        }

        return false;
    }
}
