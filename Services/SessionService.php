<?php

namespace Services;

use Core\Services\Services;
use Session;

class SessionService extends Services
{
    public function boot()
    {
        Session::start();
    }
}
