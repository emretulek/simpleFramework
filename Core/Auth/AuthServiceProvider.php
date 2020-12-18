<?php 
/**
 * @Created 16.12.2020 17:49:12
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class AuthServiceProvider
 * @package Core\Auth
 */


namespace Core\Auth;


use Core\Services\ServiceProvider;

class AuthServiceProvider extends ServiceProvider {

    public function register()
    {
        $this->app->singleton(Auth::class, function ($app){
            return new Auth($app);
        });
    }

    public function boot()
    {
        /**
         * @var Auth $auth
         */
        $auth = $this->app->resolve(Auth::class);
        $auth->rememberMe();
    }
}
