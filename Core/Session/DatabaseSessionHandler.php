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

    public function __construct(App $app, array $config)
    {
        $this->app = $app;
        $this->table = $config['database']['table'] ?? $this->table;
        $this->lifeTime = $config['options']['gc_maxlifetime'] ?: (int)ini_get('session.gc_maxlifetime') ?: $this->lifeTime;
        $this->prefix = $config['prefix'] ? session_name() : '';
    }


    /**
     * @inheritDoc
     */
    function get(string $key): string
    {
        if ($row = $this->table()->find($key)) {

            if ($row->expire <= Era::now()->getTimestamp()) {
                $this->table()->delete($key);
                return '';
            }

            return (string)($row->data);
        }

        return '';
    }

    /**
     * @inheritDoc
     */
    function set(string $key, string $session_data): bool
    {
        $encodedData = ($session_data);
        $expire = Era::now()->addSecond($this->lifeTime)->getTimestamp();

        try {
           $this->table()->insert([
                'session_id' => $key,
                'data' => $encodedData,
                'expire' => $expire,
                'userID' => $this->auth()->userID(),
                'ip' => $this->request()->forwardedIp(),
                'user_agent' => $this->request()->userAgent(),
                'referer' => $this->request()->referer()
            ]);
        } catch (SqlErrorException $e) {

            $this->table()->where('session_id', $key)->update([
                'data' => $encodedData,
                'expire' => $expire,
                'userID' => $this->auth()->userID(),
                'ip' => $this->request()->forwardedIp(),
                'user_agent' => $this->request()->userAgent(),
                'referer' => $this->request()->referer() ?: '{{referer}}'
            ]);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    function delete(string $key): bool
    {
        return (bool)$this->table()->delete($key);
    }


    /**
     * @inheritDoc
     */
    public function gc($max_lifetime): bool
    {
        $this->table()->where('expire', '<=', Era::now()->getTimestamp())->delete();
        return true;
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
    protected function auth():Auth
    {
        return $this->app->resolve(Auth::class);
    }

    /**
     * @return Request
     */
    protected function request():Request
    {
        return $this->app->resolve(Request::class);
    }

}
