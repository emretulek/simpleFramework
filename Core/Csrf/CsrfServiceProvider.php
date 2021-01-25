<?php

namespace Core\Csrf;

use Core\Services\ServiceProvider;

class CsrfServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->singleton(Csrf::class, function ($app) {
            return new Csrf($app);
        });
    }
}
