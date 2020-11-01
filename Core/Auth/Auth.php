<?php

namespace Core\Auth;


use Core\Crypt\Hash;
use Core\Database\DB;
use Core\Http\Request;
use Core\Session\Session;
use Core\Cookie\Cookie;

/**
 * Class AuthServices
 *
 * Güvenilir ve hızlı kimlik doğrulama araçları sunar.
 */
class Auth
{
    /**
     * Bilgileri girilen kullanıcı için oturum verilerini ayarlar.
     * @param int $id
     * @param string $username
     * @param string $email
     * @param string $password
     * @param int $group
     * @param array $userInfo
     * @param int $remember
     * @return int
     */
    public static function login(int $id, string $username, string $email, string $password, int $group, $userInfo = [], $remember = 0)
    {
        Session::set('AUTH.LOGIN', true);
        self::setInfo('id', $id);
        self::setInfo('username', $username);
        self::setInfo('email', $email);
        self::setInfo('group', $group);
        self::setInfo('permissions', self::userPermissions($group));
        self::setInfo('token', self::creatToken($id, $password));
        self::setInfo('ip', Request::ip());

        if ($userInfo) {
            foreach ($userInfo as $key => $value) {
                self::setInfo($key, $value);
            }
        }

        if ($remember) {
            self::creatRememberCookie($id, $username, $password, $remember);
        }

        return $id;
    }

    /**
     * Cookie kullanarak login methodunun üzerinden otomatik giriş yapar.
     * @return bool|int
     */
    public static function rememberMe()
    {
        if (Cookie::get('username') && Cookie::get('user_token') && Session::get('AUTH.LOGIN') == false) {

            if($user = DB::getRow("Select * from users where userName = ? and status = ?", [Cookie::get('username'), 1])){

                if(Cookie::get('user_token') == self::creatToken($user->userID, $user->userPassword)) {

                    return self::login($user->userID, $user->userName, $user->userEmail, $user->userPassword, $user->userGroup);
                }
            }
        }

        return false;
    }


    /**
     * AuthServices oturumunun başlatılıp başlatılmadığını kontrol eder.
     * @return bool
     */
    public static function check()
    {
        ///* Session hijacking  security */
        if (Cookie::get('username') && Cookie::get('user_token') && Auth::info('token') != Cookie::get('user_token')) {
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
    public static function userGroupName()
    {
        return Session::get('AUTH.USER.permissions.groupName');
    }


    /**
     * $gruopName group ismi veya group isimlerinden oluşan bir dizi olabilir.
     * KUllanıcı bu gruoplardan birine aitse true aksi halde false döner.
     * @param string|array $groupName
     * @return bool
     */
    public static function guard($groupName)
    {
        $groupName = is_array($groupName) ? $groupName : [$groupName];

        if (self::check() && in_array(self::userGroupName(), $groupName)) {
            return true;
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
        if(self::check()) {
            $permissions = is_array($permissions) ? $permissions : [$permissions];
            $perm = array_intersect($permissions, self::info('permissions.permissions'));

            if($perm == $permissions){
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
    public static function logout(bool $clear_all = false)
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
    private static function creatRememberCookie(int $userID, string $userName, string $userPassword, $lifetime)
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
    private static function creatToken(int $userID, string $userPassword)
    {
        return Hash::makeWithKey($userID . $userPassword . Request::userAgent());
    }


    /**
     * Gruba ait izin ve bilgileri döndürür
     * @param int $groupID
     * @return array [groupID, groupName, permissions]
     */
    private static function userPermissions(int $groupID)
    {
        $permissions['groupID'] = $groupID;
        $permissions['groupName'] = DB::getVar("Select groupName from user_groups where groupID = ?", [$groupID]);
        $permissions['permissions'] = [];

        $groupPerms = DB::get("Select up.permID, up.permName from user_group_perm ugp 
                JOIN user_permissions up ON ugp.permID = up.permID WHERE ugp.groupID = ?", [$groupID]);

        foreach ($groupPerms as $groupPerm){
            $permissions['permissions'][$groupPerm->permID] = $groupPerm->permName;
        }

        return $permissions;
    }
}

