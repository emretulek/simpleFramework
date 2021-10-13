<?php 

namespace Middleware;


use Csrf;

class CsrfToken {

    function before()
    {
        if (request()->isAjax() && Csrf::checkHeader()) {
            echo jsonError("Csrf protection.", "");
            exit;
        }
    }
}
