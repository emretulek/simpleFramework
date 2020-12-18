<?php 
/**
 * @Created 15.12.2020 21:15:17
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class FilterServiceProvider
 * @package Core\Validation
 */


namespace Core\Validation;


use Core\Http\Request;
use Core\Services\ServiceProvider;

class FilterServiceProvider extends ServiceProvider {

    public function register()
    {
        $this->app->bind(Filter::class, function ($app){

            return new Filter($app->resolve(Request::class)->request());
        });
    }
}
