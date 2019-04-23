<?php

namespace Services;

use Core\Services\Services;
use Core\Http\Request;
use Core\Language\Language;


class LanguageService extends Services
{
    public function boot()
    {
        /*
         * Kullanılabilir dilleri ve ayarlarını yükle
         */
        Language::add('tr', 'Türkçe', 'tr_TR');
        Language::add('en', 'English', 'en_US');

        $segments = Request::segments();

        if(isset($segments[0]) && Language::exists($segments[0])) {

            Language::set($segments[0]);

            if(array_shift($segments) == Language::default()){
                redirect(implode('/', $segments));
            }
        }else{
            Language::set(Language::default());
        }
    }
}
