<?php

namespace Core\Language;

use Core\Services\ServiceProvider;

class LanguageServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Language::class, function () {
            return new Language();
        });
    }
}
