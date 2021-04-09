<?php
namespace Middleware;

use Auth;

Class AuthAdminCheck {

    public function before()
    {
        if(!Auth::guard("admin")){
            Auth::logout();
            redirect('/login');
        }
    }
}
