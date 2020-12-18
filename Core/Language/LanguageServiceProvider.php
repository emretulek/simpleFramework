<?php 
/**
 * @Created 15.12.2020 08:09:05
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class LanguageServiceProvider
 * @package Core\Language
 */


namespace Core\Language;


use Core\Services\ServiceProvider;

class LanguageServiceProvider extends ServiceProvider {

    public function register()
    {
        $this->app->singleton(Language::class, function ($app){
            return new Language($app);
        });
    }
}
