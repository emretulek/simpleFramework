<?php

namespace Core\View;

use Core\Services\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(View::class, function ($app) {
            return new View($app);
        });
    }
}
