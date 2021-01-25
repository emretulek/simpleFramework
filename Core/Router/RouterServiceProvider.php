<?php

namespace Core\Router;

use Core\Services\ServiceProvider;

class RouterServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Router::class, function ($app) {
            return new Router($app);
        });
    }
}
