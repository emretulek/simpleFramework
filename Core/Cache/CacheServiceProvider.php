<?php

namespace Core\Cache;

use Core\Connector\MemcachedConnector;
use Core\Connector\RedisConnector;
use Core\Database\Database;
use Core\Services\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->registerFileCache();
        $this->registerApcuCache();
        $this->registerMemcachedCache();
        $this->registerDatabaseCache();
        $this->registerRedisCache();
        $this->registerNullCache();

        //default cache driver registered
        $this->app->singleton(Cache::class, function ($app) {

            $defaultDriver = 'Core\\Cache\\' . ucfirst($app->config['cache']['driver']) . 'Cache';

            return new Cache($this->app->resolve($defaultDriver));
        });
    }

    /**
     * file cache singleton
     */
    protected function registerFileCache()
    {
        $this->app->singleton(FileCache::class, function ($app) {
            return new FileCache($app->config['cache']['file']);
        });
    }

    /**
     * Apcu cache singleton
     */
    protected function registerApcuCache()
    {
        $this->app->singleton(ApcuCache::class, function () {
            return new ApcuCache();
        });
    }

    /**
     * memcached singleton
     */
    protected function registerMemcachedCache()
    {
        $this->app->singleton(MemcachedCache::class, function ($app) {
            $memcachedDriver = $app->config['memcached'][$app->config['cache']['memcached']];
            return new MemcachedCache(new MemcachedConnector(), $memcachedDriver);
        });
    }

    /**
     * database cache singleton
     */
    protected function registerDatabaseCache()
    {
        $this->app->singleton(DatabaseCache::class, function ($app) {
            return new DatabaseCache($this->app->resolve(Database::class), $app->config['cache']['database']);
        });
    }

    /**
     * Redis cache singleton
     */
    protected function registerRedisCache()
    {
        $this->app->singleton(RedisCache::class, function ($app) {
            $redisDriver = $app->config['redis'][$app->config['cache']['redis']];
            return new RedisCache(new RedisConnector(), $redisDriver);
        });
    }

    /**
     * Null cache singleton
     */
    protected function registerNullCache()
    {
        $this->app->singleton(NullCache::class, function () {
            return new NullCache();
        });
    }
}
