<?php


namespace Services;


use Core\Services\Services;
use Core\Auth\Auth;
use DB;

class AuthServices extends Services
{
    protected function boot()
    {
        if($userID = Auth::rememberMe()){

            $user = DB::getRow("Select * from users where userID = ?", [$userID]);
            Auth::setInfo("email", $user->userEmail);
        }
    }
}
