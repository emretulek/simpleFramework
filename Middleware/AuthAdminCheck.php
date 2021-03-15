<?php
namespace Middleware;

use Auth;
use Core\Middleware\IMiddlewareBefore;

Class AuthAdminCheck Implements IMiddlewareBefore {

    public function before()
    {
        if(!Auth::guard("admin")){
            Auth::logout();
            redirect('/login');
        }
    }
}
