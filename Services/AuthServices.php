<?php


namespace Services;


use Core\Services\Services;
use Core\Auth\Auth;
use Core\Auth\User;
use DB;
use Exception;
use Exceptions;

class AuthServices extends Services
{
    /**
     * @return mixed|void
     */
    protected function boot()
    {
        try {
            Auth::loginCookie(function ($username) {
                if ($user = DB::getRow("select * from users where userName = ?", [$username])) {
                    return new User($user->userID, $user->userName, $user->userPassword, $user->userLevel);
                }
                return false;
            });
        } catch (Exception $e) {
            Exceptions::debug($e);
        }
    }
}
