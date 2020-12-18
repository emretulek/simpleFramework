<?php 
/**
 * @Created 08.12.2020 15:05:41
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class SessionServiceProvider
 * @package Core\Session
 */


namespace Core\Session;


use Core\Services\ServiceProvider;

class SessionServiceProvider extends ServiceProvider {

    public function register()
    {
        $this->app->singleton(Session::class, function ($app){
            return new Session();
        });
    }

    public function boot()
    {
        $this->app->resolve(Session::class)->start();
    }
}
