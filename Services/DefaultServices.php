<?php

namespace Services;

use Core\Csrf\Csrf;
use Core\Services\Services;
use Core\Session\Session;

class DefaultServices extends Services
{
    public function boot()
    {
        Session::start();
        Csrf::generateToken();
    }
}
