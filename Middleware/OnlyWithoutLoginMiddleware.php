<?php 

namespace Middleware;


use Auth;

class OnlyWithoutLoginMiddleware {

    public function before()
    {
        if(Auth::check()){
            redirect('/');
        }
    }
}
