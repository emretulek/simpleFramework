<?php


namespace Core\Cache;


use Closure;
use Core\Config\Config;
use Core\Exceptions\Exceptions;
use Exception;

class Cache
{

    private static ?CacheInterface $instance = null;


    /**
     * @return CacheInterface|DatabaseCache|FileCache|MemoryCache|null
     */
    public static function init()
    {
        $config = Config::get('app.cache');

        if (self::$instance == null && $config['enable']) {

            try {
                switch ($config['driver']) {
                    case 'memcache':
                        self::$instance = new MemoryCache();
                        break;
                    case 'database':
                        self::$instance = new DatabaseCache();
                        break;
                    case 'file':
                        self::$instance = new FileCache();
                        break;
                    default :
                        throw new Exception("app.config hatalı driver tütü [memcache, database, file] kullanılabilir.", E_ERROR);
                }
            }catch (Exception $e){
                Exceptions::debug($e);
            }
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
        if ($cache = self::init()) {
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
        try {
            if ($cache = self::init()) {
                return $cache->set($key, $value, $compress, $expires);
            }
        } catch (Exception $e) {
            Exceptions::debug($e);
        }

        return false;
    }


    /**
     * @param $key
     * @return bool|mixed
     */
    public static function get($key)
    {
        if ($cache = self::init()) {
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
        if ($cache = self::init()) {
            return $cache->delete($key);
        }

        return false;
    }


    /**
     * @return bool
     */
    public static function flush()
    {
        try {
            if ($cache = self::init()) {
                return $cache->flush();
            }
        } catch (Exception $e) {
            Exceptions::debug($e);
        }

        return false;
    }

    /**
     *
     * @param $key
     * @param Closure $closure
     * @param bool $compress
     * @param int $expires
     * @return bool|mixed
     */
    public static function use($key, Closure $closure, $compress = false, $expires = 2592000)
    {
        if ($cache = self::get($key)) {
            return $cache;
        }

        $cache = call_user_func($closure);

        if (self::set($key, $cache, $compress, $expires)) {
            return $cache;
        }

        return $cache;
    }
}
