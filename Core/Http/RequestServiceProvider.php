<?php 
/**
 * @Created 05.12.2020 18:53:06
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class RequestServiceProvider
 * @package Core\Http
 */


namespace Core\Http;


use Core\Services\ServiceProvider;

class RequestServiceProvider extends ServiceProvider {

    public function register()
    {
        $this->app->singleton(Request::class, function ($app){
            return new Request($app->config['app']['path']);
        });
    }
}
