<?php

namespace Core\Auth;

use Closure;
use Core\Crypt\Hash;
use Core\Http\Request;
use Core\Session\Session;
use Core\Cookie\Cookie;
use Exception;

/**
 * Class AuthServices
 *
 * Güvenilir ve hızlı kimlik doğrulama araçları sunar.
 */
class Auth
{
    //izinler ve isimleri
    public static array $permissionList = [
        //"permName" => permID
        "login" => 1
    ];

    //grouplar ve yetkileri
    public static array $groups = [
        'user' => [0],
        'admin' => [1]
        ];

    public static function login(User $user, $remember = 24)
    {
        Session::set('AUTH.LOGIN', true);
        self::setInfo('id', $user->id);
        self::setInfo('username', $user->username);
        self::setInfo('group', $user->group);
        self::setInfo('token', self::creatToken($user->id, $user->password));
        self::setInfo('ip', Request::ip());

        if ($user->info) {
            foreach ($user->info as $key => $value) {
                self::setInfo($key, $value);
            }
        }

        if ($remember) {
            self::remember($user->id, $user->username, $user->password, $remember);
        }

        return true;
    }

    /**
     * Cookie kullanarak login methodunun üzerinden otomatik giriş yapar.
     * @param Closure $closure parametre olarak username alır, Auth\User sınıfını return etmelidir.
     * @throws Exception
     * @return bool
     *
     */
    public static function loginCookie(Closure $closure)
    {
        if (!self::status() && Cookie::get('username') && Cookie::get('user_token')) {

            if($user = call_user_func($closure, Cookie::get('username'))) {

                if ($user instanceof User) {

                    if (Cookie::get('user_token') == self::creatToken($user->id, $user->password)) {
                        return self::login($user);
                    }
                } else {
                    throw new Exception("Callback User sınıfını return etmelidir.", E_ERROR);
                }
            }
        }

        return false;
    }


    /**
     * AuthServices oturumunun başlatılıp başlatılmadığını kontrol eder.
     * @return bool
     */
    public static function status()
    {
        ///* Session hijacking  security */
        if (Cookie::get('user_token') && Auth::info('token') != Cookie::get('user_token')) {
            self::logout();
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
    public static function userName()
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
    public static function userGroup()
    {
        return Session::get('AUTH.USER.group');
    }


    /**
     * $gruopName group ismi veya group isimlerinden oluşan bir dizi olabilir.
     * KUllanıcı bu gruoplardan birine aitse true aksi halde false döner.
     * @param string|array $groupName
     * @return bool
     */
    public static function guard($groupName)
    {
        if(is_array($groupName)) {
            if (self::status() && in_array(self::userGroup(), $groupName)) {
                return true;
            }
        }else{
            if (self::status() && strcasecmp(self::userGroup(), $groupName) == 0) {
                return true;
            }
        }
        return false;
    }


    /**
     * $permissions bir izin adı veya izinlerden oluşan bir dizi
     * Belirtilen tüm izinlere sahipse true aksi ahalde false döner
     * @param string|array $permissions
     * @return bool
     */
    public static function permission($permissions)
    {
        $permissions = is_array($permissions) ? $permissions : [$permissions];

        if(self::status() && is_array($permissions) && !empty($permissions)) {
            $perm = array_intersect_key(self::$permissionList, array_flip($permissions));
            $intersect = array_intersect($perm, self::$groups[self::userGroup()]);
            if($perm == $intersect){
                return true;
            }
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
        return Hash::makeWithKey($userID . $userPassword . Request::userAgent());
    }
}

