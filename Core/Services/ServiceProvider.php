<?php

namespace Core\Services;

use Core\App;

/**
 * Class ServiceProvider
 */
abstract class ServiceProvider
{
    protected App $app;

    /**
     * ServiceProvider constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * register new provider
     */
    public function register()
    {

    }

    /**
     * boot runing with application start
     */
    public function boot()
    {

    }
}

