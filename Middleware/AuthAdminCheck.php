<?php

namespace Middleware;

use Auth;
use Core\Middleware\IMiddlewareBefore;

class AuthAdminCheck implements IMiddlewareBefore
{
    public function before()
    {
        if (!Auth::guard("admin")) {
            viewPath('errors/403')->render();
            exit;
        }
    }
}
