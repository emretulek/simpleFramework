<?php

namespace Core\Cache;

use Closure;
use Core\Connector\RedisConnector;
use Redis;

class RedisCache extends BaseCache
{

    protected Redis $redis;

    public function __construct(RedisConnector $redis, array $config)
    {
        $this->redis = $redis->connect($config['server'], $config['port'], $config['options']);
    }

    /**
     *  Önbellekten ilgili anahtara ait değeri döndürür
     * @param string $key
     * @param null $default
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function get(string $key, $default = null)
    {
        if ($value = $this->redis->get($key)) {
            return unserialize($value);
        }

        if ($default instanceof Closure) {
            return $default();
        }

        return $default;
    }

    /**
     * Önbelleğe yeni bir değer ekler, anahtar varsa üzerine yazar
     *
     * @param string $key
     * @param $value
     * @param int|null|\DateInterval $ttl
     * @return bool
     * @throws InvalidArgumentException
     */
    public function set(string $key, $value, $ttl = null): bool
    {
        return $this->redis->set($key, serialize($value), $this->ttl($ttl)?:null);
    }

    /**
     * Önbelleğe yeni bir değer ekler, anahtar varsa false döner
     *
     * @param string $key
     * @param $value
     * @param int|null|\DateInterval $ttl
     * @return bool
     * @throws InvalidArgumentException
     */
    public function add(string $key, $value, $ttl = null): bool
    {
        if ($this->has($key)) {
            return false;
        }

        return $this->set($key, $value, $ttl);
    }

    /**
     * Önbellekte veri varsa getirir yoksa oluşturuğ default değeri döndürür
     * @param string $key
     * @param int|null|\DateInterval $ttl
     * @param mixed|Closure $default
     * @return mixed
     */
    public function getSet(string $key, $ttl = null, $default = null)
    {
        if ($value = $this->get($key)) {
            return $value;
        }

        if ($default instanceof Closure) {
            $value = $default();
        } else {
            $value = $default;
        }

        $this->set($key, $value, $ttl);

        return $value;
    }

    /**
     * Önbellekten ilgili anahtara ait değeri siler
     *
     * @param string $key
     * @return bool
     * @throws InvalidArgumentException
     */
    public function delete(string $key): bool
    {
        return $this->redis->del($key);
    }

    /**
     * Tüm önbelleği temizler
     * @return bool
     */
    public function clear(): bool
    {
        return $this->redis->flushDB();
    }

    /**
     * Çoklu önbellek listesi
     * @param array $keys anahtar değer ilişkili liste
     * @return array A list of key
     * @throws InvalidArgumentException
     */
    public function getMultiple(array $keys): array
    {
        $results = [];
        $values = $this->redis->mget($keys);

        foreach ($values as $index => $value) {
            $results[$keys[$index]] = !is_null($value) ? unserialize($value) : null;
        }

        return $results;
    }

    /**
     * Çoklu önbellekleme
     * @param array $items anahtar değer ilişkili liste
     * @param int|null|\DateInterval $ttl geçerlilik süresi
     * @return bool
     * @throws InvalidArgumentException
     */
    public function setMultiple(array $items, $ttl = null): bool
    {
        $this->redis->multi();
        $result = [];
        foreach ($items as $key => $value) {
            $result[] = $this->set($key, $value, $ttl);
        }
        $this->redis->exec();

        return !in_array(false, $result, true);
    }

    /**
     * Çoklu önbellekten veri silme
     * @param array $keys
     * @return bool
     * @throws InvalidArgumentException
     */
    public function deleteMultiple(array $keys): bool
    {
        return $this->redis->del($keys);
    }

    /**
     * Önbellekte anahtarın olup olmadığını kontrol eder
     * @param string $key
     * @return bool
     * @throws InvalidArgumentException
     */
    public function has(string $key): bool
    {
        return (bool)$this->redis->exists('key');
    }

    /**
     * @param string $key
     * @param int $value
     * @return int|false
     */
    public function increment(string $key, $value = 1)
    {
        return $this->redis->incrby($key, $value);
    }

    /**
     * @param string $key
     * @param int $value
     * @return int|false
     */
    public function decrement(string $key, $value = 1)
    {
        return $this->redis->decrBy($key, $value);
    }
}
