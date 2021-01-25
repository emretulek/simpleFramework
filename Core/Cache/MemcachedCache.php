<?php

namespace Core\Cache;

use Closure;
use DateInterval;
use DateTime;
use Memcached;

class MemcachedCache implements CacheInterface
{

    protected Memcached $memcached;

    /**
     * MemcachedCache constructor.
     * @param MemcachedConnector $memcached
     * @param array $config
     */
    public function __construct(MemcachedConnector $memcached, array $config)
    {
        $this->memcached = $memcached->connect(
            $config['servers'], $config['connection_id'], $config['options'], $config['sasl']
        );
    }

    /**
     * @param string $key
     * @param mixed|Closure $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $value = $this->memcached->get($key);

        if ($this->memcached->getResultCode() == 0) {
            return $value;
        }

        if ($default instanceof Closure) {
            return $default();
        }

        return $default;
    }

    /**
     * @param string $key
     * @param $value
     * @param \DateInterval|int|null $ttl
     * @return bool
     */
    public function set(string $key, $value, $ttl = null): bool
    {
        return $this->memcached->set($key, $value, $this->timeout($ttl));
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
        return $this->memcached->add($key, $value, $this->timeout($ttl));
    }

    /**
     * @param string $key
     * @param \DateInterval|int|null $ttl
     * @param mixed|Closure $default
     * @return mixed|null
     */
    public function getSet(string $key, $ttl = null, $default = null)
    {
        $value = $this->memcached->get($key);

        if ($this->memcached->getResultCode() == 0) {
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
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        $result = $this->memcached->delete($key);

        if ($this->memcached->getResultCode() == Memcached::RES_NOTFOUND) {
            return true;
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function clear(): bool
    {
        return $this->memcached->flush();
    }

    /**
     * @param array $keys
     * @return array
     */
    public function getMultiple(array $keys): array
    {
        $values = $this->memcached->getMulti($keys, Memcached::GET_PRESERVE_ORDER);

        if ($this->memcached->getResultCode() != 0) {
            return array_fill_keys($keys, null);
        }

        return array_combine($keys, $values);
    }

    /**
     * @param array $items
     * @param \DateInterval|int|null $ttl
     * @return bool
     */
    public function setMultiple(array $items, $ttl = null): bool
    {
        return $this->memcached->setMulti($items, $this->timeout($ttl));
    }

    /**
     * @param array $keys
     * @return bool
     */
    public function deleteMultiple(array $keys): bool
    {
        $values = $this->memcached->deleteMulti($keys);
        $expected = array_fill_keys($keys, Memcached::RES_SUCCESS);

        return $values == $expected;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        $this->memcached->get($key);
        return $this->memcached->getResultCode() == 0;
    }


    /**
     * @param string $key
     * @param int $value
     * @return false|int
     */
    public function increment(string $key, $value = 1)
    {
        return $this->memcached->increment($key, $value);
    }


    /**
     * @param string $key
     * @param int $value
     * @return false|int
     */
    public function decrement(string $key, $value = 1)
    {
        return $this->memcached->decrement($key, $value);
    }

    /**
     * @param $timeout
     * @return mixed
     */
    private function timeout($timeout): int
    {
        $timeout = empty($timeout) ? '999999999' : $timeout;

        $date = new DateTime();

        if ($timeout instanceof DateInterval) {
            $date->add($timeout);
        } else {
            $dateInterval = new DateInterval("PT{$timeout}S");
            $date->add($dateInterval);
        }

        return $date->format("U");
    }
}
