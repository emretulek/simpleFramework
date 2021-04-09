<?php
namespace Middleware;

use Auth;

Class AuthCheck {

    public function before()
    {
        if(Auth::check()){
            Auth::logout(true);
            redirect('/login');
        }
    }
}
