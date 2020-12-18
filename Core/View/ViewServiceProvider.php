<?php 
/**
 * @Created 07.12.2020 20:26:35
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class ViewServiceProvider
 * @package Core\View
 */


namespace Core\View;


use Core\Services\ServiceProvider;

class ViewServiceProvider extends ServiceProvider {

    public function register()
    {
        $this->app->singleton(View::class, function ($app){
           return new View($app);
        });
    }
}
