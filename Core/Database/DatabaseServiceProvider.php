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

            $selectConnectionType = '\\Core\\Database\\' . ucfirst($driver) . 'Connection';
            $connectionConfig = $app->config['database'][$driver];

            return new Database(new $selectConnectionType($connectionConfig), $app);
        });
    }
}
