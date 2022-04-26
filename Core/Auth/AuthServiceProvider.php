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

        $this->app->singleton(AuthJWT::class, function ($app) {
            return new AuthJWT($app);
        });
    }

    public function boot()
    {
        /**
         * @var Auth $auth
         */
        $AUTH_TYPE = $this->app->config['auth']['authorization_type'];

        if($AUTH_TYPE == 'AuthJWT'){
            $auth = $this->app->resolve(AuthJWT::class);
        }else {
            $auth = $this->app->resolve(Auth::class);
            $auth->rememberMe();
        }
    }
}
