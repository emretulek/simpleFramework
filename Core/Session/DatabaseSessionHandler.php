<?php


namespace Core\Session;


use Core\App;
use Core\Auth\Auth;
use Core\Database\Database;
use Core\Database\QueryBuilder;
use Core\Database\SqlErrorException;
use Core\Era\Era;
use Core\Http\Request;


class DatabaseSessionHandler extends BaseSessionHandler
{

    private App $app;
    protected string $table = 'sessions';
    protected int $lifeTime = 78840000;
    protected int $blockingTimeout = -1;

    public function __construct(App $app, array $config)
    {
        $this->app = $app;
        $this->table = $config['database']['table'] ?? $this->table;
        $this->lifeTime = $config['options']['gc_maxlifetime'] ?: (int)ini_get('session.gc_maxlifetime') ?: $this->lifeTime;
        $this->prefix = $config['prefix'] ? session_name() : '';
        $this->blockingTimeout = $config['database']['blockingTimeout'] ?? $this->blockingTimeout;
    }


    /**
     * @inheritDoc
     */
    function get(string $key): string
    {
        $this->table()->database()->getVar("SELECT GET_LOCK(?, ?) AS free", [$key, $this->blockingTimeout]);

        $row = $this->table()->find($key);
        return (string)isset($row->data) ? $row->data : '';
    }

    /**
     * @inheritDoc
     */
    function set(string $key, string $session_data): bool
    {
        $expire = Era::now()->addSecond($this->lifeTime)->getTimestamp();

        try {
            $this->table()->insert([
                'session_id' => $key,
                'data' => $session_data,
                'expire' => $expire,
                'userID' => $this->auth()->userID(),
                'ip' => $this->request()->forwardedIp(),
                'user_agent' => $this->request()->userAgent(),
                'referer' => $this->request()->referer()
            ]);
        } catch (SqlErrorException $e) {

            $this->table()->where('session_id', $key)->update([
                'data' => $session_data,
                'expire' => $expire,
                'userID' => $this->auth()->userID(),
                'ip' => $this->request()->forwardedIp(),
                'user_agent' => $this->request()->userAgent(),
                'referer' => $this->request()->referer() ?: '{{referer}}'
            ]);
        }

        $this->table()->database()->getVar("DO RELEASE_LOCK(?)", [$key]);

        return true;
    }

    /**
     * @inheritDoc
     */
    function delete(string $key): bool
    {
        try {
            $this->table()->delete($key);
            return true;
        } catch (SqlErrorException $e) {
            $this->app->debug($e);
            return false;
        }
    }


    /**
     * @inheritDoc
     */
    public function gc($max_lifetime): bool
    {
        try {
            $this->table()->where('expire', '<=', Era::now()->getTimestamp())->delete();
            return true;
        } catch (SqlErrorException $e) {
            $this->app->debug($e);
            return false;
        }
    }


    /**
     * @return \Core\Database\QueryBuilder
     */
    protected function table(): QueryBuilder
    {
        return $this->app->resolve(Database::class)->table($this->table)->pk('session_id');
    }

    /**
     * @return Auth
     */
    protected function auth(): Auth
    {
        return $this->app->resolve(Auth::class);
    }

    /**
     * @return Request
     */
    protected function request(): Request
    {
        return $this->app->resolve(Request::class);
    }
}
