<?php

namespace Services;

use Core\Language\Language;
use Core\Services\Services;



class LanguageService extends Services
{
    public function boot()
    {
        //default dil aktif edildi. config/app.php -> language
        Language::init();

        //kullanılabilir dillere yeni dil eklendi
        //Language::add('en', 'English', 'en_US');

        //default dil olarak ingilizce belirtildi
        //Language::setDefault('en');

        //ingilizce seçili dil olarak işaretlendi
        //Language::setActive('en');

        //Seçili dil url adresinden algılansın
        //Language::useUrl();
    }
}
