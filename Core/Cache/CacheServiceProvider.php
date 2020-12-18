<?php 
/**
 * @Created 13.12.2020 17:54:35
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class CacheServiceProvider
 * @package Core\Cache
 */


namespace Core\Cache;


use Core\Database\Database;
use Core\Services\ServiceProvider;

class CacheServiceProvider extends ServiceProvider {

    public function register()
    {
        $this->registerFileCache();
        $this->registerApcuCache();
        $this->registerMemcachedCache();
        $this->registerDatabaseCache();
        $this->registerRedisCache();

        //default cache driver registered
        $this->app->singleton(Cache::class, function ($app){

            $defaultDriver = 'Core\\Cache\\'.ucfirst($app->config['app']['cache_driver']).'Cache';

            return new Cache($this->app->resolve($defaultDriver));
        });
    }

    /**
     * file cache singleton
     */
    protected function registerFileCache()
    {
        $this->app->singleton(FileCache::class, function($app){
           return new FileCache($app->config['cache']['file']);
        });
    }

    protected function registerApcuCache()
    {
        $this->app->singleton(ApcuCache::class, function(){
            return new ApcuCache();
        });
    }

    /**
     * memcached singleton
     */
    protected function registerMemcachedCache()
    {
        $this->app->singleton(MemcachedCache::class, function($app){
            return new MemcachedCache(new MemcachedConnector(), $app->config['cache']['memcached']);
        });
    }

    /**
     * database cache singleton
     */
    protected function registerDatabaseCache()
    {
        $this->app->singleton(DatabaseCache::class, function($app){
            return new DatabaseCache($this->app->resolve(Database::class), $app->config['cache']['database']);
        });
    }

    /**
     * Redis cache singleton
     */
    protected function registerRedisCache()
    {
        $this->app->singleton(RedisCache::class, function($app){
            return new RedisCache(new RedisConnector(), $app->config['cache']['redis']);
        });
    }
}
