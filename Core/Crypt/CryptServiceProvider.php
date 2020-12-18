<?php 
/**
 * @Created 14.12.2020 19:03:20
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class CryptServiceProvider
 * @package Core\Crypt
 */


namespace Core\Crypt;


use Core\Services\ServiceProvider;

class CryptServiceProvider extends ServiceProvider {

    public function register()
    {
        $this->registerCrypt();
        $this->registerHash();
    }

    /**
     * Register Crypt class
     */
    public function registerCrypt(){
        $this->app->singleton(Crypt::class, function ($app){
            return new Crypt($app->config['app']['app_key'], $app->config['app']['encrypt_algo'], $app->config['app']['hash_algo']);
        });
    }

    /**
     * register Hash class
     */
    public function registerHash(){
        $this->app->singleton(Hash::class, function ($app){
            return new Hash($app->config['app']['app_key'], $app->config['app']['hash_algo'], $app->config['app']['password_hash']);
        });
    }
}
