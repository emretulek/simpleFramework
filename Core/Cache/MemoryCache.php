<?php


namespace Core\Cache;


use Core\Config\Config;
use Core\Exceptions\Exceptions;
use Exception;
use Memcache;


class MemoryCache implements CacheInterface
{

    protected Memcache $memcache;

    public function __construct()
    {
        try {

            if (!extension_loaded('memcache')) {
                throw new Exception('memcache eklentisi kurulu değil.', E_ERROR);
            }

            $this->memcache = new Memcache();
            $this->memcache->connect(Config::get('app.cache.memcache.host'), Config::get('app.cache.memcache.port'));

        }catch (Exception $e){
            Exceptions::debug($e);
        }
    }

    /**
     * Önbelleğe yeni bir değer ekler, anahtar varsa eklemez false döndürür
     *
     * @param $key
     * @param $value
     * @param bool $compress
     * @param int $expires
     * @return bool
     */
    public function add($key, $value, $compress = false, $expires = 2592000): bool
    {
        if($this->memcache) {
            return $this->memcache->add($key, $value, $compress, $expires);
        }

        return false;
    }

    /**
     * Önbelleğe yeni bir değer ekler, anahtar varsa üzerine yazar
     *
     * @param $key
     * @param $value
     * @param bool $compress
     * @param int $expires
     * @return bool
     */
    public function set($key, $value, $compress = false, $expires = 2592000): bool
    {
        if($this->memcache) {
            return $this->memcache->set($key, $value, $compress, $expires);
        }

        return false;
    }

    /**
     * Önbellekten ilgili anahtara ait değeri döndürür
     *
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        if($this->memcache) {
            return $this->memcache->get($key);
        }

        return false;
    }

    /**
     * Önbellekten ilgili anahtara ait değeri siler
     *
     * @param $key
     * @return bool
     */
    public function delete($key): bool
    {
        if($this->memcache) {
            return $this->memcache->delete($key);
        }

        return false;
    }

    /**
     * Tüm önbelleği temizler
     *
     * @return bool
     */
    public function flush(): bool
    {
        if($this->memcache) {
            return $this->memcache->flush();
        }

        return false;
    }
}
