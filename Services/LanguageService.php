<?php

namespace Services;

use Core\Services\ServiceProvider;
use Language;

class LanguageService extends ServiceProvider
{
    public function boot()
    {
        Language::load();
//        //kullanılabilir diller arasına ingilizce ekleniyor
//        Language::add('en', 'English', 'EN-us');
    }
}
