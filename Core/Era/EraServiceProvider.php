<?php


namespace Core\Era;


use Core\Services\ServiceProvider;

class EraServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(Era::class, function ($app){
            return (new Era('now', $app->config['app']['timezone']))
                ->locale($app->config['app']['language']['locale'].'.'.$app->config['app']['charset']);
        });
    }

    public function boot()
    {
        $this->app->resolve(Era::class);
    }
}
