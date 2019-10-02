<?php

namespace Services;

use App\Model\Users;
use Core\Services\Services;
use Session;
use Auth;

class RememberMe extends Services
{
    public function boot()
    {
        if (!Auth::status()) {
            // Beni hatırla seçeneğini seçenler için otomatik giriş
            Auth::loginCookie(function ($username) {
                if($userInfo = Users::static()->findFromUserName($username)) {
                    return [
                        'id' => $userInfo->userID,
                        'username' => $userInfo->userName,
                        'level' => $userInfo->userLevel
                    ];
                }
                return false;
            });
        }
    }
}
