<?php

namespace Core\Auth;


use Core\App;
use Core\Cookie\Cookie;
use Core\Crypt\Hash;
use Core\Database\Database;
use Core\Database\QueryBuilder;
use Core\Database\SqlErrorException;
use Core\Http\Request;
use Core\Session\Session;


/**
 * Class AuthServiceProvider
 *
 * Güvenilir ve hızlı kimlik doğrulama araçları sunar.
 */
class Auth
{
    protected App $app;

    protected string $table = 'users';

    protected string $tokenName = 'user_token';

    protected ?object $user = null;

    /**
     * login olacak kulanıcılarda aranan temel kriterler
     * @var array
     */
    public array $userWhere = [
        'deleted_at' => null,
        'status' => 1
    ];

    protected array $noStoreSession = [
        'userID',
        'username',
        'email',
        'group',
        'password'
    ];


    /**
     * Auth constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;

        if ($this->check()) {
            $this->user = $this->table()->select()->where('userID', $this->userID())->getRow();
            $this->generateUserSessions();
        }
    }

    /**
     * @param string $password
     * @param string $userName
     * @param int $remember
     * @param array $where
     * @return bool
     */
    public function loginWithUserName(string $password, string $userName, $remember = 0, array $where = []): bool
    {
        $userInfo = ['username' => $userName];
        $userInfo = array_merge($userInfo, $where);

        return $this->login($password, $userInfo, $remember);
    }

    /**
     * @param string $password
     * @param string $email
     * @param int $remember
     * @param array $where
     * @return bool
     */
    public function loginWithEmail(string $password, string $email, $remember = 0, array $where = []): bool
    {
        $userInfo = ['email' => $email];
        $userInfo = array_merge($userInfo, $where);

        return $this->login($password, $userInfo, $remember);
    }

    /**
     * @param string $password
     * @param string $userNameOrEmail
     * @param int $remember
     * @param array $where
     * @return bool
     */
    public function loginWithEmailOrUserName(string $password, string $userNameOrEmail, $remember = 0, array $where = []): bool
    {
        if (filter_var($userNameOrEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->loginWithEmail($password, $userNameOrEmail, $remember, $where);
        } else {
            return $this->loginWithUserName($password, $userNameOrEmail, $remember, $where);
        }
    }


    /**
     * @param $password
     * @param $userInfo
     * @param int $remember
     * @return bool
     * @throws SqlErrorException
     */
    public function login($password = null, $userInfo = [], $remember = 0): bool
    {
        if ($password) {
            $this->setUserForPassword($password, $userInfo);
        } elseif (isset($userInfo['rememberme'])) {
            $this->setUserForToken($userInfo);
        }

        if (isset($this->user)) {

            $this->generateUserSessions();

            if ($remember) {
                $this->updateRememberToken($this->creatToken());
                $this->creatRememberCookie($this->creatToken(), $remember);
            }

            return true;
        }

        return false;
    }


    /**
     * kullanıcı sessionb ilgilerini oluşturur
     */
    protected function generateUserSessions()
    {
        $this->authSession('LOGIN', true);
        $this->authSessionUser('id', $this->user->userID);
        $this->authSessionUser('username', $this->user->username);
        $this->authSessionUser('email', $this->user->email);
        $this->authSessionUser('roleID', $this->user->roleID);
        $this->authSessionUser('permissions', $this->userPermissions());
        $this->authSessionUser($this->tokenName, $this->creatToken());
        $this->authSessionUser('ip', $this->app->resolve(Request::class)->ip());

        foreach ($this->user as $key => $value) {
            if (!in_array($key, $this->noStoreSession)) {
                $this->authSessionUser($key, $value);
            }
        }
    }


    /**
     * Password ile login olacak kullanıcı bilgileri
     * @param string $password
     * @param array $userInfo
     * @return bool
     * @throws SqlErrorException
     */
    protected function setUserForPassword(string $password, array $userInfo): bool
    {
        $userInfo = array_merge($this->userWhere, $userInfo);

        $query = $this->table()->select();

        //kullanıcı varsa
        if ($user = $query->where($userInfo)->getRow()) {
            //şifre kontrolü
            if ($rehash = $this->app->resolve(Hash::class)->passwordCheck($password, $user->password)) {
                //rehash gerekliyse şifre update edilir
                if ($rehash != $user->password) {
                    $this->table()->where('userID', $user->userID)->update(['password', $rehash]);
                }

                $this->user = $user;
                return true;
            }
        }

        return false;
    }

    /**
     * Token ile login olacak kullanıcı bilgileri
     * @param array $userInfo
     * @return bool
     * @throws SqlErrorException
     */
    protected function setUserForToken(array $userInfo)
    {
        $userInfo = array_merge($this->userWhere, $userInfo);

        $query = $this->table()->select();

        if ($user = $query->where($userInfo)->getRow()) {
            $this->user = $user;
            return true;
        }

        return false;
    }


    /**
     * @param string $token
     * @throws SqlErrorException
     */
    protected function updateRememberToken(string $token)
    {
        $this->table()
            ->where('userID', $this->user->userID)
            ->update(['rememberme' => $token]);
    }


    /**
     * @return array|false|mixed|object
     * @throws SqlErrorException
     */
    public function rememberMe()
    {
        $usertoken = $this->app->resolve(Cookie::class)->get($this->tokenName);

        if ($usertoken && $this->authSession('LOGIN') == false) {

            return $this->login(null, ['rememberme' => $usertoken]);
        }

        return false;
    }


    /**
     * Oturumunun başlatılıp başlatılmadığını kontrol eder.
     * @return bool
     */
    public function check(): bool
    {
        /* Session hijacking  security */
        if ($this->app->resolve(Cookie::class)->get($this->tokenName) &&
            $this->info($this->tokenName) != $this->app->resolve(Cookie::class)->get($this->tokenName)) {

            $this->logout();
            return false;
        }

        return (bool)$this->authSession('LOGIN');
    }

    /**
     * @return object|null
     */
    public function user(): ?object
    {
        return $this->user;
    }

    /**
     * $_SESSSION['AUTH']['USER'] değişkeninden kullanıcı bilgilerine erişir.
     * @param null $key
     * @return mixed
     */
    public function info($key = null)
    {
        if ($key === null) {
            return $this->authSession('USER');
        }
        return $this->authSessionUser($key);
    }


    /**
     * Sessiondan id değerini döndürür.
     * @return bool|mixed
     */
    public function userID(): int
    {
        return $this->authSessionUser('id');
    }

    /**
     * @return bool|mixed
     */
    public function role()
    {
        return $this->authSessionUser('roleID');
    }

    /**
     * Sessiondan yetki seviyesini döndürür.
     * @return null|string Sessiona ulaşamazsa false döndürür.
     */
    public function roleName()
    {
        return $this->authSessionUser('permissions.role_name');
    }


    /**
     * $gruopName group ismi veya group isimlerinden oluşan bir dizi olabilir.
     * KUllanıcı bu gruoplardan birine aitse true aksi halde false döner.
     * @param string|array $roleName
     * @return bool
     */
    public function guard($roleName): bool
    {
        $roleName = is_array($roleName) ? $roleName : [$roleName];

        if ($this->check() && in_array($this->roleName(), $roleName)) {
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
    public function permission($permissions): bool
    {
        if ($this->check()) {
            $permissions = is_array($permissions) ? $permissions : [$permissions];
            $perm = array_intersect($permissions, $this->info('permissions.permissions'));

            if ($perm == $permissions) {
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
    public function logout(bool $clear_all = false)
    {
        $this->app->resolve(Session::class)->remove('AUTH');
        $this->app->resolve(Cookie::class)->remove($this->tokenName);

        if ($clear_all) {
            $this->app->resolve(Session::class)->destroy();
            $this->app->resolve(Cookie::class)->destroy();
        }
    }


    /**
     * Beni hatırla seçeneği için cookie oluşturur.
     * @param string $token
     * @param int|string $lifetime strtottime veya int saniye
     */
    protected function creatRememberCookie(string $token, $lifetime)
    {
        $this->app->resolve(Cookie::class)->set($this->tokenName, $token, $lifetime);
    }


    /**
     * Cookie doğrulaması useragent ile token oluşturur.
     * @return string
     */
    protected function creatToken(): string
    {
        return md5($this->user->userID . $this->user->password . $this->app->resolve(Request::class)->userAgent());
    }


    /**
     * Gruba ait izin ve bilgileri döndürür
     * @return array [groupID, groupName, permissions]
     */
    protected function userPermissions()
    {
        $permissions['roleID'] = $this->user->roleID;
        $permissions['role_name'] = $this->getRoleName();
        $permissions['permissions'] = [];

        $groupPerms = $this->app->resolve(Database::class)->table('role_permissions rp')
            ->join('permissions p', 'rp.permissionID = p.permissionID')
            ->select('p.permissionID, p.perm_name')->where('rp.roleID', $this->user->roleID)->get();

        foreach ($groupPerms as $groupPerm) {
            $permissions['permissions'][$groupPerm->permissionID] = $groupPerm->name;
        }

        return $permissions;
    }


    /**
     * @return mixed
     * @throws SqlErrorException
     */
    protected function getRoleName()
    {
        return $this->app->resolve(Database::class)->table("user_roles")
            ->select('role_name')
            ->where('roleID', $this->user->roleID)
            ->getVar();
    }


    /**
     * @param $key
     * @param null $value
     * @return bool|mixed
     */
    protected function authSessionUser($key, $value = null)
    {
        return $this->authSession('USER.' . $key, $value);
    }

    /**
     * @param $key
     * @param null $value
     * @return bool|mixed
     */
    protected function authSession($key, $value = null)
    {
        if ($value === null) {
            return $this->app->resolve(Session::class)->get('AUTH.' . $key);
        } else {
            return $this->app->resolve(Session::class)->set('AUTH.' . $key, $value);
        }
    }

    /**
     * @return QueryBuilder
     */
    protected function table(): QueryBuilder
    {
        return $this->app->resolve(Database::class)->table($this->table);
    }
}

