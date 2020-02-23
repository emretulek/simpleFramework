<?php


namespace Services;


use Auth;
use Core\Services\Services;
use DB;
use Exception;
use Exceptions;

class RememberMe extends Services
{
    /**
     * @return mixed|void
     */
    protected function boot()
    {
        try {
            Auth::loginCookie(function ($username) {
                if ($user = DB::getRow("select * from users where userName = ?", [$username])) {
                    return [
                        'id' => $user->userID,
                        'username' => $user->userName,
                        'password' => $user->userPassword,
                        'userLevel' => $user->userLevel,
                        'info' => []
                    ];
                }
                return false;
            });
        } catch (Exception $e) {
            Exceptions::debug($e);
        }
    }
}
