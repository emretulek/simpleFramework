<?php

namespace Services;

use Core\Model\Settings;
use Config;
use Core\Services\ServiceProvider;
use Csrf;

class DefaultServiceProvider extends ServiceProvider
{

    public function register()
    {
       //
    }

    public function boot()
    {
        Csrf::generateToken();
        $this->loadSettingsFromDatabase();
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
}
