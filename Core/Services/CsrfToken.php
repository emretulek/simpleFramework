<?php


namespace Core\Services;

use Core\Csrf\Csrf;


class CsrfToken extends Services
{
    public function boot()
    {
        Csrf::generateToken();
    }
}
