<?php
namespace Middleware;

use Auth;
use Core\Middleware\IMiddleware;

Class AuthCheck Implements IMiddleware {

    public function handle()
    {
        if(!Auth::guard(1)){
            redirect('login');
        }
    }
}
