<?php 

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
