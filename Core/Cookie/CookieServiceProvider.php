<?php

namespace Core\Cookie;

use Core\Http\Request;
use Core\Services\ServiceProvider;

class CookieServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->singleton(Cookie::class, function ($app) {
            return new Cookie($app->resolve(Request::class));
        });
    }
}
