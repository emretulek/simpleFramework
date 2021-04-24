<?php


namespace Core\Cache;


use Core\Era\Era;
use DateInterval;

abstract class BaseCache implements CacheInterface
{
    /**
     * @param null|int|DateInterval $ttl
     * @return int
     */
    protected function ttl($ttl = null): int
    {
        if (is_null($ttl)) {
            $ttl = 0;
        }

        if ($ttl instanceof DateInterval) {
            $ttl = Era::dateIntervalTo($ttl, 'second');
        }

        return (int)$ttl;
    }

    /**
     * @param $ttl
     * @return int
     * @throws \Exception
     */
    protected function expires($ttl): int
    {
        $seconds = $this->ttl($ttl);
        $seconds = $seconds ?: 315360000;
        return Era::now()->addSecond($seconds)->getTimestamp();
    }

    /**
     * @inheritDoc
     */
    abstract public function get(string $key, $default = null);

    /**
     * @inheritDoc
     */
    abstract public function set(string $key, $value, $ttl = null): bool;

    /**
     * @inheritDoc
     */
    abstract public function add(string $key, $value, $ttl = null): bool;

    /**
     * @inheritDoc
     */
    abstract public function getSet(string $key, $ttl = null, $default = null);

    /**
     * @inheritDoc
     */
    abstract public function delete(string $key): bool;

    /**
     * @inheritDoc
     */
    abstract public function clear(): bool;

    /**
     * @inheritDoc
     */
    abstract public function getMultiple(array $keys): array;

    /**
     * @inheritDoc
     */
    abstract public function setMultiple(array $items, $ttl = null): bool;

    /**
     * @inheritDoc
     */
    abstract public function deleteMultiple(array $keys): bool;

    /**
     * @inheritDoc
     */
    abstract public function has(string $key): bool;

    /**
     * @inheritDoc
     */
    abstract public function increment(string $key, $value = 1);

    /**
     * @inheritDoc
     */
    abstract public function decrement(string $key, $value = 1);
}
