<?php

namespace Services;

use Core\Services\ServiceProvider;
use Language;

class LanguageService extends ServiceProvider
{
    public function boot()
    {
        //kullanılabilir diller arasına ingilizce ekleniyor
        Language::add('en', 'English', 'EN-us');
    }
}
