<?php

namespace Core\Services;

use Core\Session\Session;

class SessionService extends Services
{
    public function boot()
    {
        Session::start();
    }
}
