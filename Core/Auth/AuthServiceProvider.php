<?php


namespace Core\Auth;


use Core\Services\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Auth::class, function ($app) {
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
