<?php 

namespace Middleware;


use Csrf;

class CsrfToken {

    function before()
    {
        if (Csrf::checkPost()) {
            echo jsonError("Csrf protection.", "");
            exit;
        }
    }
}
