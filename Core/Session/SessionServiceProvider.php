<?php

namespace Core\Session;

use Core\Services\ServiceProvider;

class SessionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Session::class, function ($app) {
            return new Session(new SessionManager($app), $app);
        });
    }

    public function boot()
    {
        $this->app->resolve(Session::class)->start();
    }
}
