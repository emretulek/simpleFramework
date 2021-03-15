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
        'deleted_at' => null
    ];

    protected array $noStoreSession = [
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
            $result = $this->setUserForPassword($password, $userInfo);
        } elseif (isset($userInfo['rememberme'])) {
            $result = $this->setUserForToken($userInfo);
        }else{
            $result = false;
        }

        if ($result) {
            $this->generateUserSessions();

            if ($remember) {
                $token = $this->creatToken();
                $this->creatRememberCookie($token, $remember);
                $this->table()
                    ->where('userID', $this->user->userID)
                    ->update(['rememberme' => $token]);
            }

            $this->table()
                ->where('userID', $this->user->userID)
                ->update(['session_id' => session_id(), 'last_login' => '{{NOW()}}']);
        }

        return $result;
    }


    /**
     * kullanıcı sessionb ilgilerini oluşturur
     */
    protected function generateUserSessions()
    {
        $this->authSession('LOGIN', true);
        $this->authSession('id', $this->user->userID);
        $this->authSession('username', $this->user->username);
        $this->authSession('email', $this->user->email);
        $this->authSession('roleID', $this->user->roleID);
        $this->authSession('permissions', $this->userPermissions());
        $this->authSession($this->tokenName, $this->creatToken());
        $this->authSession('ip', $this->request()->ip());

        foreach ($this->user as $key => $value) {
            if (!in_array($key, $this->noStoreSession)) {
                $this->authSession($key, $value);
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
            /* Session hijacking  security */
            if($this->creatToken() === $this->user->rememberme){
                return true;
            }
        }

        return false;
    }


    /**
     * @return array|false|mixed|object
     * @throws SqlErrorException
     */
    public function rememberMe()
    {
        if ($this->cookie()->get($this->tokenName) && !$this->check()) {

            return $this->login(null, ['rememberme' => $this->cookie()->get($this->tokenName)]);
        }

        return false;
    }


    /**
     * Oturumunun başlatılıp başlatılmadığını kontrol eder.
     * @return bool
     */
    public function check(): bool
    {
        return (bool) $this->authSession('LOGIN');
    }

    /**
     * @return object|null
     */
    public function user(): ?object
    {
        return $this->user;
    }

    /**
     * Sessiondan id değerini döndürür.
     * @return bool|mixed
     */
    public function userID(): int
    {
        return $this->authSession('id');
    }

    /**
     * @return bool|mixed
     */
    public function role()
    {
        return $this->authSession('roleID');
    }

    /**
     * Sessiondan yetki seviyesini döndürür.
     * @return null|string Sessiona ulaşamazsa false döndürür.
     */
    public function roleName()
    {
        return $this->authSession('permissions.role_name');
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
            $perm = array_intersect($permissions, $this->authSession('permissions.permissions'));

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
        $this->session()->remove('AUTH');
        $this->cookie()->remove($this->tokenName);

        if ($clear_all) {
            $this->session()->destroy();
            $this->cookie()->destroy();
        }
    }


    /**
     * Beni hatırla seçeneği için cookie oluşturur.
     * @param string $token
     * @param int|string $lifetime strtottime veya int saniye
     */
    protected function creatRememberCookie(string $token, $lifetime)
    {
        $this->cookie()->set($this->tokenName, $token, $lifetime);
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

        $rolePerms = $this->app->resolve(Database::class)->table('role_permissions rp')
            ->join('permissions p', 'rp.permissionID = p.permissionID')
            ->select('p.permissionID, p.perm_name')->where('rp.roleID', $this->user->roleID)->get();

        foreach ($rolePerms as $rolePerm) {
            $permissions['permissions'][$rolePerm->permissionID] = $rolePerm->perm_name;
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
    public function authSession($key, $value = null)
    {
        if ($value === null) {
            return $this->session()->get('AUTH.USER.' . $key);
        } else {
            return $this->session()->set('AUTH.USER.' . $key, $value);
        }
    }


    /**
     * @return QueryBuilder
     */
    protected function table(): QueryBuilder
    {
        return $this->app->resolve(Database::class)->table($this->table);
    }


    /**
     * @return Session
     */
    protected function session():Session
    {
        return $this->app->resolve(Session::class);
    }

    /**
     * @return Cookie
     */
    protected function cookie():Cookie
    {
        return $this->app->resolve(Cookie::class);
    }

    /**
     * @return Request
     */
    protected function request():Request
    {
        return $this->app->resolve(Request::class);
    }
}

