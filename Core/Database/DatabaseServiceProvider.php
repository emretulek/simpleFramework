<?php

namespace Core\Database;

use Core\Services\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->selectDriver(Database::class, $this->app->config['app']['sql_driver']);
    }

    /**
     * register selected driver
     * @param $name
     * @param $driver
     */
    protected function selectDriver($name, $driver)
    {
        $this->app->singleton($name, function ($app) use ($driver) {

            $selectConnectionType = '\\Core\\Database\\' . ucfirst($app->config['database'][$driver]['driver']) . 'Connection';

            return new Database(new $selectConnectionType($app->config['database'][$driver]));
        });
    }
}
