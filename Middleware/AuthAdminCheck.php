<?php
namespace Middleware;

use Auth;
use Core\Middleware\IMiddleware;

Class AuthAdminCheck Implements IMiddleware {

    public function before()
    {
        if(!Auth::guard("admin")){
            redirect('403');
        }
    }

    public function after()
    {
        // TODO: Implement after() method.
    }
}
