<?php
namespace Middleware;

use Auth;
use Core\Middleware\IMiddlewareBefore;

Class AuthCheck Implements IMiddlewareBefore {

    public function before()
    {
        if(Auth::check()){
            if(Auth::user()->status == 0){
                redirect('/activation');
            }
            if(Auth::user()->status == -1){
                Auth::logout(true);
                redirect('/login');
            }
        }

        if(!Auth::guard( ["member", "admin"])){
            Auth::logout();
            redirect('/login');
        }
    }
}
