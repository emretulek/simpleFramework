<?php


namespace Core\Session;


use Core\Cache\MemcachedCache;


class MemcachedSessionHandler extends BaseSessionHandler
{
    protected MemcachedCache $memcachedCache;
    protected int $lifeTime = 78840000;

    public function __construct(MemcachedCache $memcachedCache, array $config)
    {
        $this->memcachedCache = $memcachedCache;
        $this->lifeTime = (int)ini_get('session.gc_maxlifetime') ?: $this->lifeTime;
        $this->prefix = $config['prefix'] ? session_name() : '';
    }


    /**
     * @inheritDoc
     */
    function get(string $key): string
    {
        return $this->memcachedCache->get($key, '');
    }

    /**
     * @inheritDoc
     */
    function set(string $key, string $session_data): bool
    {
        return $this->memcachedCache->set($key, $session_data, $this->lifeTime);
    }

    /**
     * @inheritDoc
     */
    function delete(string $key): bool
    {
        return $this->memcachedCache->delete($key);
    }
}
