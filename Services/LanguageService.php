<?php

namespace Services;

use Core\Config\Config;
use Core\Http\Request;
use Core\Language\Language;
use Core\Services\Services;


class LanguageService extends Services
{
    public function boot()
    {
        /**
         * default dil yükleme
         */
        Language::add(Config::get('app.language.key'), Config::get('app.language.name'), Config::get('app.language.local'));
        Language::setDefault(Config::get('app.language.key'));

        /*
         * Kullanılabilir dilleri ve ayarlarını yükle
         */
        Language::add('en-us', 'English', 'en_US');

        $segments = Request::segments();

        if(isset($segments[0])) {

            Language::set($segments[0]);

            //default dil ise adres satırında gösterme
            if (array_shift($segments) == Language::getDefault()->key) {
                redirect(Request::baseUrl().implode('/', $segments));
            }
        }
    }

}
