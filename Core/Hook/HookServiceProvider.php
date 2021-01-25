<?php

namespace Core\Hook;

use Core\Services\ServiceProvider;

class HookServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Hook::class, function () {
            return new Hook();
        });
    }
}
