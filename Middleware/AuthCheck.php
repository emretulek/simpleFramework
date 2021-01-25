<?php

namespace Middleware;

use Auth;
use Core\Middleware\IMiddlewareBefore;

class AuthCheck implements IMiddlewareBefore
{
    public function before()
    {
        if (!Auth::check()) {
            redirect('login');
        }
    }
}
