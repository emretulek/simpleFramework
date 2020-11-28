<?php
namespace Middleware;

use Auth;
use Core\Middleware\IMiddlewareBefore;

Class AuthAdminCheck Implements IMiddlewareBefore {

    public function before()
    {
        if(!Auth::guard("admin")){
            viewPath('errors/403')->render();
            exit;
        }
    }
}
