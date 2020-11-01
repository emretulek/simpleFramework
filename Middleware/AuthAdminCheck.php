<?php
namespace Middleware;

use Auth;
use Core\Middleware\IMiddleware;

Class AuthAdminCheck Implements IMiddleware {

    public function before()
    {
        if(!Auth::guard("admin")){
            viewPath('errors/403', [], '.html')->render();
            exit;
        }
    }
}
