<?php
namespace Middleware;

use Core\Csrf\Csrf;
use Core\Middleware\IMiddleware;

Class CsrfCheck Implements IMiddleware {

    public function before()
    {
        Csrf::check();
    }

    public function after()
    {

    }
}
