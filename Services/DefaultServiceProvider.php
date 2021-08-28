<?php

namespace Services;

use Core\Model\Settings;
use Config;
use Core\Services\ServiceProvider;
use Csrf;
use Language;

class DefaultServiceProvider extends ServiceProvider
{

    public function register()
    {
       //
    }

    public function boot()
    {
        //csrf open
        //Csrf::generateToken();

        //load site settings
        //$this->loadSettingsFromDatabase();

        //load other languages
        //$this->loadLanguages();
    }

    /**
     * veritabanından ayarlarının yüklenmesi
     */
    protected function loadSettingsFromDatabase()
    {
        $settings = Settings::select()->get();

        foreach ($settings as $setting){
            Config::set('settings.'.$setting->name, $setting->value);
        }
    }

    protected function loadLanguages()
    {
        //kullanılabilir diller arasına ingilizce ekleniyor
        Language::add('en', 'English', 'EN_us');
    }
}
