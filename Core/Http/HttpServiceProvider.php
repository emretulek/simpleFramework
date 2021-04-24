<?php

namespace Core\Http;

use Core\Services\ServiceProvider;

class HttpServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Request::class, function ($app) {
            return new Request($app->config['app']['path']);
        });

        $this->app->singleton(Response::class);
    }
}
