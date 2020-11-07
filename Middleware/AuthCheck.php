<?php
namespace Middleware;

use Auth;
use Core\Middleware\IMiddlewareBefore;

Class AuthCheck Implements IMiddlewareBefore {

    public function before()
    {
        if(!Auth::check()){
            redirect('login');
        }
    }
}
