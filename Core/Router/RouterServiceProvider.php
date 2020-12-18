<?php 
/**
 * @Created 05.12.2020 21:34:21
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class RouterServiceProvider
 * @package Core\Router
 */


namespace Core\Router;


use Core\Services\ServiceProvider;

class RouterServiceProvider extends ServiceProvider {

    public function register()
    {
        $this->app->singleton(Router::class, function ($app){
            return new Router($app);
        });
    }
}
