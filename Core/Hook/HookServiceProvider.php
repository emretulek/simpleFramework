<?php 
/**
 * @Created 15.12.2020 00:37:33
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class HookServiceProvider
 * @package Core\Hook
 */


namespace Core\Hook;


use Core\Services\ServiceProvider;

class HookServiceProvider extends ServiceProvider {

    public function register()
    {
        $this->app->singleton(Hook::class, function (){
            return new Hook();
        });
    }
}
