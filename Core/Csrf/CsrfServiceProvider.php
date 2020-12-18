<?php 
/**
 * @Created 15.12.2020 00:07:58
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class CsrfServiceProvider
 * @package Core\Csrf
 */


namespace Core\Csrf;


use Core\Services\ServiceProvider;

class CsrfServiceProvider extends ServiceProvider {

    public function register()
    {
        $this->app->singleton(Csrf::class, function($app){
            return new Csrf($app);
        });
    }
}
