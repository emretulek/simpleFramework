<?php

namespace Core\Auth;


use Core\App;
use Core\Crypt\Hash;
use Core\Database\Database;
use Core\Database\QueryBuilder;
use Core\Database\SqlErrorException;
use Core\Http\Request;
use DateInterval;
use Core\Era\Era;
use Exception;
use Firebase\JWT\JWT;


/**
 * Class AuthServiceProvider
 *
 * Güvenilir ve hızlı kimlik doğrulama araçları sunar.
 */
class AuthJWT
{
    protected App $app;

    protected string $table = 'users';

    protected ?object $user = null;

    protected array $payload = [];

    protected string $secret;

    protected string $algo = 'HS256';

    protected string $jwtHeader = 'Authorization';

    /**
     * login olacak kulanıcılarda aranan temel kriterler
     * @var array
     */
    public array $userWhere = [
        'deleted_at' => null,
        'status'     => 1
    ];

    use Attempt;

    /**
     * @param App $app
     * @throws SqlErrorException
     */
    public function __construct(App $app)
    {
        $this->app = $app;

        $this->table     = $this->app->config['auth']['table'] ?? $this->table;
        $this->userWhere = $this->app->config['auth']['user_where'] ?? $this->userWhere;

        $this->attempt        = $this->app->config['auth']['login_attempt'] ?? $this->attempt;
        $this->attemptMax     = $this->app->config['auth']['login_attempt_max'] ?? $this->attemptMax;
        $this->attemptTimeout = $this->app->config['auth']['login_attempt_timeout'] ?? $this->attemptTimeout;

        $this->secret    = $this->app->config['auth']['jwt']['secret'];
        $this->algo      = $this->app->config['auth']['jwt']['algo'];
        $this->payload   = $this->app->config['auth']['jwt']['payload'];
        $this->jwtHeader = $this->app->config['auth']['jwt']['header'];
    }

    /**
     * @param string $password
     * @param string $userName
     * @param int|DateInterval|Era $remember
     * @param array $where
     * @return string
     * @throws AuthError
     * @throws SqlErrorException
     */
    public function loginWithUserName(string $password, string $userName, $remember = 0, array $where = []): string
    {
        $userInfo = ['username' => $userName];
        $userInfo = array_merge($userInfo, $where);

        return $this->login($password, $userInfo, $remember);
    }

    /**
     * @param string $password
     * @param string $email
     * @param int|DateInterval|Era $remember
     * @param array $where
     * @return bool
     * @throws AuthError
     * @throws SqlErrorException
     */
    public function loginWithEmail(string $password, string $email, $remember = 0, array $where = []): string
    {
        $userInfo = ['email' => $email];
        $userInfo = array_merge($userInfo, $where);

        return $this->login($password, $userInfo, $remember);
    }

    /**
     * @param string $password
     * @param string $userNameOrEmail
     * @param int|DateInterval|Era $remember
     * @param array $where
     * @return bool
     * @throws AuthError
     * @throws SqlErrorException
     */
    public function loginWithEmailOrUserName(string $password, string $userNameOrEmail, $remember = 0, array $where = []): string
    {
        if (filter_var($userNameOrEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->loginWithEmail($password, $userNameOrEmail, $remember, $where);
        } else {
            return $this->loginWithUserName($password, $userNameOrEmail, $remember, $where);
        }
    }


    /**
     * @param string $password
     * @param array $userInfo
     * @param int|DateInterval|Era $remember
     * @return string JWT
     * @throws AuthError
     * @throws SqlErrorException
     * @throws Exception
     */
    public function login(string $password, array $userInfo = [], $remember = 0): string
    {
        $user = $this->findUserWithPassword($password, $userInfo);

        if ($remember) {
            $this->payload['exp'] = $this->payload['iat'] + $remember;
        }

        $this->payload['data'] = [
            'userID' => $user->userID
        ];

        $jwtToken = JWT::encode($this->payload, $this->secret, $this->algo);

        $this->table()->where('userID', $user->userID)->update([
            'last_login' => Era::now(),
            'ip'         => $this->request()->ip()
        ]);

        return $jwtToken;
    }


    /**
     * Password ile login olacak kullanıcı bilgileri
     * @param string $password
     * @param array $userInfo
     * @return array|mixed|string|string[]|null
     * @throws AuthError
     * @throws SqlErrorException
     */
    protected function findUserWithPassword(string $password, array $userInfo)
    {
        $userInfo = array_merge($this->userWhere, $userInfo);

        //login attempt
        if ($this->checkAttempt(implode($userInfo))) {
            throw new AuthError(AuthError::TOOMANYATTEMPTS);
        }

        if (!$user = $this->table()->where($userInfo)->getRow()) {
            $this->setAttempt(implode($userInfo));
            throw new AuthError(AuthError::USER_NOT_FOUND);
        }

        //şifre kontrolü
        if (!$rehash = $this->app->resolve(Hash::class)->passwordCheck($password, $user->password)) {
            $this->setAttempt(implode($userInfo));
            throw new AuthError(AuthError::PASSWORD_MISMATCH);
        }

        //rehash gerekliyse şifre update edilir
        if ($rehash != $user->password) {
            $this->table()->where('userID', $user->userID)->update(['password', $rehash]);
        }

        $this->clearAttempt(implode($userInfo));

        return $user;
    }


    /**
     * Oturumunun başlatılıp başlatılmadığını kontrol eder.
     * @return bool
     */
    public function check(): bool
    {
        try {
            $data       = JWT::decode($this->getToken(), $this->secret, [$this->algo]);
            $this->user = $this->table()->select()->where('userID', $data->data->userID)->getRow();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }


    /**
     * @return false|object
     */
    public function getData()
    {
        try {
            $data = JWT::decode($this->getToken(), $this->secret, [$this->algo]);
        } catch (Exception $e) {
            return false;
        }

        return $data;
    }


    /**
     * @return string
     */
    protected function getToken(): string
    {
        $bearer = request()->server($this->jwtHeader);
        return str_replace("Bearer ", "", $bearer);
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
     * @return bool|int
     */
    public function userID()
    {
        return $this->user->id;
    }

    /**
     * @return bool|mixed
     */
    public function role()
    {
        return $this->user->roleID;
    }

    /**
     * Sessiondan yetki seviyesini döndürür.
     * @return null|string Sessiona ulaşamazsa false döndürür.
     */
    public function roleName(): ?string
    {
        $permissions = $this->userPermissions($this->user->roleID);
        return $permissions['role_name'];
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
            $userPermissions = $this->userPermissions($this->user->roleID);
            $permissions     = is_array($permissions) ? $permissions : [$permissions];
            $perm            = array_intersect($permissions, $userPermissions['permissions']);

            if ($perm == $permissions) {
                return true;
            }
        }

        return false;
    }


    /**
     * Gruba ait izin ve bilgileri döndürür
     * @param $roleID
     * @return array [groupID, groupName, permissions]
     */
    protected function userPermissions($roleID): array
    {
        $permissions['roleID']      = $roleID;
        $permissions['role_name']   = $this->getRoleName($roleID);
        $permissions['permissions'] = [];

        try {
            $rolePerms = $this->app->resolve(Database::class)->table('role_permissions rp')
                                   ->join('permissions p', 'rp.permissionID = p.permissionID')
                                   ->select('p.permissionID, p.perm_name')->where('rp.roleID', $roleID)->get();

            foreach ($rolePerms as $rolePerm) {
                $permissions['permissions'][$rolePerm->permissionID] = $rolePerm->perm_name;
            }

        } catch (SqlErrorException $e) {
            debug($e);
        }

        return $permissions;
    }


    /**
     * @return mixed
     */
    protected function getRoleName($roleID)
    {
        try {
            return $this->app->resolve(Database::class)->table("user_roles")
                             ->select('role_name')
                             ->where('roleID', $roleID)
                             ->getVar();
        } catch (Exception $e) {
            debug($e);
        }

        return '';
    }


    /**
     * @return QueryBuilder
     */
    protected function table(): QueryBuilder
    {
        return $this->app->resolve(Database::class)->table($this->table);
    }


    /**
     * @return Request
     */
    protected function request(): Request
    {
        return $this->app->resolve(Request::class);
    }
}

