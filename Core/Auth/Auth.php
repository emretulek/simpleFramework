<?php

namespace Core\Auth;

use Core\Http\Request;
use Core\Session\Session;
use Core\Cookie\Cookie;
use Core\Config\Config;

/**
 * Class Auth
 *
 * Güvenilir ve hızlı kimlik doğrulama araçları sunar.
 */
class Auth
{

    /**
     *
     * @param int $id
     * @param string $username
     * @param int $level
     * @param array|null $info
     * @param bool $remember
     * @return bool
     */
    public static function login(int $id, string $username, int $level = 1, array $info = null, bool $remember = false)
    {
        Session::set('AUTH.LOGIN', true);
        self::setInfo('id', $id);
        self::setInfo('username', $username);
        self::setInfo('level', $level);
        self::setInfo('token', self::creatToken($id));
        self::setInfo('ip', Request::ip());

        if ($info) {
            foreach ($info as $key => $value) {
                self::setInfo($key, $value);
            }
        }

        if ($remember) {
            self::remember($id, $username);
        }
        return true;
    }

    /**
     * Cookie kullanarak login methodunun üzerinden otomatik giriş yapar.
     *
     * @param callable $callback parametre olarak username alır.
     * return [id][username][level][info[..]] dizisini bekler.
     * @return bool
     */
    public static function loginCookie(callable $callback)
    {
        if (!Cookie::get('username') || !Cookie::get('user_token')) {
            return false;
        }

        if ($userInfo = call_user_func($callback, Cookie::get('username'))) {

            $token = self::creatToken($userInfo['id']);

            if ($token == Cookie::get('user_token')) {

                $userInfo['level'] = $userInfo['level'] ?? 1;
                $userInfo['info'] = $userInfo['info'] ?? null;

                return self::login($userInfo['id'], $userInfo['username'], $userInfo['level'], $userInfo['info']);
            }
        }
        return false;
    }


    /**
     * Auth oturumunun başlatılıp başlatılmadığını kontrol eder.
     * @return bool
     */
    public static function status()
    {
        ///* Session hijacking  security */
        if (Auth::info('token') != Cookie::get('user_token') && Cookie::get('user_token')) {
            Auth::logout(true);
        }

        return Session::get('AUTH.LOGIN');
    }

    /**
     * $_SESSSION['AUTH']['USER'] değişkenine kullanıcı bilgilerini depolar.
     *
     * @param $key
     * @param $value
     * @return bool
     */
    public static function setInfo($key, $value)
    {
        return Session::set('AUTH.USER.' . $key, $value);
    }


    /**
     * $_SESSSION['AUTH']['USER'] değişkeninden kullanıcı bilgilerine erişir.
     *
     * @param null $key
     * @return bool|mixed
     */
    public static function info($key = null)
    {
        if ($key === null) {
            return Session::get('AUTH.USER');
        }
        return Session::get('AUTH.USER.' . $key);
    }


    /**
     * Sessiondan username değerini döndürür
     * @return bool|mixed
     */
    public static function user()
    {
        return Session::get('AUTH.USER.username');
    }

    /**
     * Sessiondan id değerini döndürür.
     * @return bool|mixed
     */
    public static function userID()
    {
        return Session::get('AUTH.USER.id');
    }

    /**
     * Sessiondan yetki seviyesini döndürür.
     * @return bool|mixed Sessiona ulaşamazsa false döndürür.
     */
    public static function level()
    {
        return Session::get('AUTH.USER.level');
    }


    /**
     * Girilen yetki seviyesi ve üzeri dışında erişime kapatır.
     * Örnek 2 girilirse alana sadece yetkisi 2 ve üzeri olanlar erişebilir.
     * @param int $level
     * @return bool
     */
    public static function guard($level = 1)
    {
        if(self::status() && $level <= self::level()){

            return true;
        }
        return false;
    }


    /**
     * Kullanıcının çıkış işlemini gerçekleştirir.
     * {false} girilir yada boş bırakılırsa AUTH bilgileri silinir.
     * {true girilirse} kullanıcıya ait tüm bilgiler silinir.
     * @param bool $clear_all
     */
    public static function logout($clear_all = false)
    {
        Session::remove('AUTH');
        Cookie::remove('username');
        Cookie::remove('user_token');

        if ($clear_all) {
            Session::destroy();
            Cookie::destroy();
        }
    }


    /**
     * Beni hatırla seçeneği için kullanıcı bilgilerini çerezlere saklar.
     * @param $userID
     * @param $userName
     */
    private static function remember($userID, $userName)
    {
        Cookie::set('user_token', self::creatToken($userID));
        Cookie::set('username', $userName);
    }


    /**
     * Cookie doğrulaması için token oluşturur.
     * @param $userID
     * @return string
     */
    private static function creatToken($userID)
    {
        $ip = md5(Request::ip());
        $agent = md5(Request::userAgent());
        $appkey = Config::get('app.app_key');
        $token = md5($ip . $agent . $appkey . $userID);

        return $token;
    }
}

