<?php

namespace Core\Http;

use Core\Services\ServiceProvider;

class RequestServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Request::class, function ($app) {
            return new Request($app->config['app']['path']);
        });
    }
}
