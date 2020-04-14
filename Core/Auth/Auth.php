<?php

namespace Core\Auth;

use Closure;
use Core\Crypt\Hash;
use Core\Http\Request;
use Core\Session\Session;
use Core\Cookie\Cookie;
use Exception;

/**
 * Class Auth
 *
 * Güvenilir ve hızlı kimlik doğrulama araçları sunar.
 */
class Auth
{

    /**
     * @param int $id
     * @param string $username
     * @param string $password
     * @param int $level
     * @param array $info
     * @param int $remember Cookie\Cookie::set @param $lifetime
     * @return bool
     */
    public static function login(int $id, string $username, string $password, int $level = 1, array $info = [], $remember = 24)
    {
        try {
            Session::set('AUTH.LOGIN', true);
            self::setInfo('id', $id);
            self::setInfo('username', $username);
            self::setInfo('level', $level);
            self::setInfo('token', self::creatToken($id, $password));
            self::setInfo('ip', Request::ip());

            if ($info) {
                foreach ($info as $key => $value) {
                    self::setInfo($key, $value);
                }
            }

            if ($remember) {
                self::remember($id, $username, $password, $remember);
            }
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Cookie kullanarak login methodunun üzerinden otomatik giriş yapar.
     * @param Closure $closure (string $username):array $userInfo
     * [id, username, password, level, info[]]
     * @throws Exception
     * @return bool
     * @todo Auth::loginCookie(function ($username){
     *      $user = DB::getRow("select * from users where userName = ?", [$username]);
     *       return [
     *       'id' => $user->userID,
     *       'username' => $user->userName,
     *       'password' => $user->userPassword,
     *       'userLevel' => $user->userLevel,
     *       'info' => []
     *       ];
     *       });
     *
     */
    public static function loginCookie(Closure $closure)
    {
        if (!self::status() && Cookie::get('username') && Cookie::get('user_token')) {

            if($userInfo = call_user_func($closure, Cookie::get('username'))) {

                if (isset($userInfo['id'], $userInfo['username'], $userInfo['password'], $userInfo['level'], $userInfo['info'])) {

                    if (Cookie::get('user_token') == self::creatToken($userInfo['id'], $userInfo['password'])) {
                        return self::login($userInfo['id'], $userInfo['username'], $userInfo['password'], $userInfo['level'], $userInfo['info']);
                    }
                } else {
                    throw new Exception("Clouser [id, username, password, level, info[]] elemanlarını barındıran bir dizi döndürmelidir.", E_ERROR);
                }
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
        if (Cookie::get('user_token') && Auth::info('token') != Cookie::get('user_token')) {
            return false;
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
        if (self::status() && $level <= self::level()) {

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
     * Beni hatırla seçeneği için cookie oluşturur.
     * @param $userID
     * @param $userName
     * @param $userPassword
     * @param $lifetime
     */
    private static function remember($userID, $userName, $userPassword, $lifetime)
    {
        Cookie::set('user_token', self::creatToken($userID, $userPassword), $lifetime);
        Cookie::set('username', $userName, $lifetime);
    }


    /**
     * Cookie doğrulaması useragent ile token oluşturur.
     * @param $userID
     * @param $userPassword
     * @return string
     */
    private static function creatToken($userID, $userPassword)
    {
        $agent = md5(Request::userAgent());
        return Hash::makeWithKey($userID . $userPassword . $agent);
    }
}

