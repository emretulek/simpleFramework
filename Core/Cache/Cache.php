<?php


namespace Core\Cache;


use Core\App;

class Cache
{

    private static $instance = false;

    /**
     * @param CacheInterface|null $cacheClass
     * @return bool|mixed
     */
    public static function init(?CacheInterface $cacheClass)
    {
        if($cacheClass instanceof  CacheInterface) {
            return self::$instance = App::getInstance($cacheClass);
        }
        return self::$instance;
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
        if($cache = self::init(null)) {
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
        if($cache = self::init(null)){
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
        if($cache = self::init(null)){
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
        if($cache = self::init(null)){
            return $cache->delete($key);
        }

        return false;
    }


    /**
     * @return bool
     */
    public static function flush()
    {
        if($cache = self::init(null)){
            return $cache->flush();
        }

        return false;
    }
}
