<?php 
/**
 * @Created 15.12.2020 19:53:50
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class LoggerServiceProvider
 * @package Core\Log
 */


namespace Core\Log;


use Core\Database\Database;
use Core\Services\ServiceProvider;

class LoggerServiceProvider extends ServiceProvider {

    public function register()
    {
        $this->app->singleton(Logger::class, function ($app){
            $driver = 'Core\\Log\\'.ucfirst($app->config['app']['logger_driver']).'Log';
            return new Logger($app->resolve($driver));
        });

        $this->app->singleton(FileLog::class, function($app){
            return new FileLog($app->config['logger']['file']);
        });

        $this->app->singleton(DatabaseLog::class, function($app){
            return new DatabaseLog(
                $app->resolve(Database::class),
                $app->config['logger']['database']
            );
        });
    }
}
