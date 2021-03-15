<?php 

namespace Middleware;


use Csrf;
use Core\Middleware\IMiddlewareBefore;

class CsrfToken implements IMiddlewareBefore {

    function before()
    {
        if (Csrf::checkPost()) {
            echo jsonError("Csrf protection.", "");
            exit;
        }
    }
}
