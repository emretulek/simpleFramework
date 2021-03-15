<?php 

namespace Middleware;


use Auth;
use Core\Middleware\IMiddlewareBefore;

class OnlyWithoutLoginMiddleware implements IMiddlewareBefore {

    public function before()
    {
        if(Auth::check()){
            redirect('/');
        }
    }
}
