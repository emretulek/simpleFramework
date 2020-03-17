<?php

namespace Core\Services;

use Core\Config\Config;
use Core\Http\Request;
use Core\Language\Language;


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
            $default = Language::getDefault();

            if (array_shift($segments) == $default['key']) {
                redirect(Request::baseUrl().implode('/', $segments));
            }
        }
    }

}
