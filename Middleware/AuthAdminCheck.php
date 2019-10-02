<?php
namespace Middleware;

use Auth;
use Core\Middleware\IMiddleware;

Class AuthAdminCheck Implements IMiddleware {

    public function handle()
    {
        if(!Auth::guard(3)){
            redirect('login');
        }
    }
}
