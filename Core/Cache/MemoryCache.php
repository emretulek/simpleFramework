<?php


namespace Core\Cache;


use Core\Config\Config;
use Core\Log\LogException;
use Memcache;


class MemoryCache implements CacheInterface
{

    protected $memcache;

    public function __construct()
    {
        try {

            if (!extension_loaded('memcache')) {
                throw new LogException('memcache eklentisi kurulu değil.', E_ERROR);
            }

            $this->memcache = new Memcache();
            $this->memcache->connect(Config::get('app.cache.memcache.host'), Config::get('app.cache.memcache.port'));

        }catch (LogException $exception){
            $exception->debug();
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
        return $this->memcache->add($key, $value, $compress, $expires);
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
        return $this->memcache->set($key, $value, $compress, $expires);
    }

    /**
     * Önbellekten ilgili anahtara ait değeri döndürür
     *
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->memcache->get($key);
    }

    /**
     * Önbellekten ilgili anahtara ait değeri siler
     *
     * @param $key
     * @return bool
     */
    public function delete($key): bool
    {
        return $this->memcache->delete($key);
    }

    /**
     * Tüm önbelleği temizler
     *
     * @return bool
     */
    public function flush(): bool
    {
        return $this->memcache->flush();
    }
}
