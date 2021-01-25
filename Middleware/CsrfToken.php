<?php

namespace Middleware;

use Csrf;
use Core\Middleware\IMiddlewareBefore;

class CsrfToken implements IMiddlewareBefore
{
    function before()
    {
        Csrf::generateToken();
    }
}
