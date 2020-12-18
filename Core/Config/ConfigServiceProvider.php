<?php 
/**
 * @Created 05.12.2020 22:30:18
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class ConfigServiceProvider
 * @package Core\Config
 */


namespace Core\Config;


use Core\Services\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider {

    public function register()
    {
        $this->app->singleton(Config::class, function ($app){
            return new Config($app->config);
        });
    }
}
