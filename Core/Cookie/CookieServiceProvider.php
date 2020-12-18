<?php 
/**
 * @Created 08.12.2020 18:30:38
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class CookieServiceProvider
 * @package Core\Cookie
 */


namespace Core\Cookie;


use Core\Http\Request;
use Core\Services\ServiceProvider;

class CookieServiceProvider extends ServiceProvider {

    public function register()
    {
        $this->app->singleton(Cookie::class, function ($app){
           return new Cookie($app->resolve(Request::class));
        });
    }
}
