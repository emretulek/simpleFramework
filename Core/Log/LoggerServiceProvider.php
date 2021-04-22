<?php

namespace Core\Log;

use Core\Database\Database;
use Core\Services\ServiceProvider;

class LoggerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Logger::class, function ($app) {
            $driver = 'Core\\Log\\' . ucfirst($app->config['logger']['driver']) . 'Log';
            return new Logger($app->resolve($driver));
        });

        //file log singleton
        $this->app->singleton(FileLog::class, function ($app) {
            return new FileLog($app->config['logger']['file']);
        });

        //database log singleton
        $this->app->singleton(DatabaseLog::class, function ($app) {
            return new DatabaseLog(
                $app->resolve(Database::class),
                $app->config['logger']['database']
            );
        });

        //null log singleton
        $this->app->singleton(NullLog::class, function () {
            return new NullLog();
        });
    }
}
