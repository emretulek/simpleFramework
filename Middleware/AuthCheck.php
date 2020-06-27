<?php
namespace Middleware;

use Auth;
use Core\Middleware\IMiddleware;

Class AuthCheck Implements IMiddleware {

    public function before()
    {
        if(!Auth::status()){
            redirect('login');
        }
    }

    public function after()
    {
        // TODO: Implement after() method.
    }
}
