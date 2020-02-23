<?php
namespace Middleware;

use Auth;
use Core\Middleware\IMiddleware;

Class AuthAdminCheck Implements IMiddleware {

    public function before()
    {
        if(!Auth::guard(3)){
            redirect('login');
        }
    }

    public function after()
    {
        // TODO: Implement after() method.
    }
}
