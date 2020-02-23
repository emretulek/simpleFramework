<?php
namespace Core\Middleware;

use Core\Csrf\Csrf;

Class CsrfCheck Implements IMiddleware {

    public function before()
    {
        Csrf::check();
    }

    public function after()
    {
        // TODO: Implement after() method.
    }
}
