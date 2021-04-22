<?php


namespace Core\Session;


use Core\Cache\RedisCache;

class RedisSessionHandler extends BaseSessionHandler
{

    protected RedisCache $redisCache;
    protected int $lifeTime = 78840000;

    public function __construct(RedisCache $redisCache, array $config)
    {
        $this->redisCache = $redisCache;
        $this->lifeTime = (int)ini_get('session.gc_maxlifetime') ?: $this->lifeTime;
        $this->prefix = $config['prefix'] ? session_name() : '';
    }


    /**
     * @inheritDoc
     */
    function get(string $key): string
    {
        return $this->redisCache->get($key, '');
    }

    /**
     * @inheritDoc
     */
    function set(string $key, string $session_data): bool
    {
        return $this->redisCache->set($key, $session_data, $this->lifeTime);
    }

    /**
     * @inheritDoc
     */
    function delete(string $key): bool
    {
        return $this->redisCache->delete($key);
    }
}
