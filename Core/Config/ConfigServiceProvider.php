<?php

namespace Core\Config;

use Core\Services\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->singleton(Config::class, function ($app) {
            return new Config($app->config);
        });
    }
}
