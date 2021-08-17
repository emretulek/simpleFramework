<?php

namespace Core\Csrf;

use Core\App;
use Core\Cookie\Cookie;
use Core\Http\Request;
use Core\Session\Session;

class Csrf
{
    private App $app;

    //default 5dk
    private int $tokenTimeout = 60 * 60;

    public function __construct(App $app)
    {
        $this->app = $app;
    }


    /**
     * @param int $seconds
     */
    public function setTokenTimeout(int $seconds)
    {
        $this->tokenTimeout = $seconds;
    }


    /**
     * Csrf için token oluşturup cookie ve sessiona atar
     * $_SESSION old_csrf_token, csrf_token
     * $_COOKIE csrf_token
     * @param bool $refresh
     */
    public function generateToken($refresh = false)
    {
        if($this->session()->get('csrf_token_timeout') < time() || $refresh == true) {
            $token = md5(uniqid());
            $this->session()->set('csrf_token', $token);
            $this->session()->set('csrf_token_timeout', time() + $this->tokenTimeout);
            $this->cookie()->set('csrf_token', $token, $this->tokenTimeout, '/', null, false, false);
        }
    }


    /**
     * @return false|mixed
     */
    public function refreshToken()
    {
        $this->generateToken(true);
        return $this->session()->get('csrf_token');
    }


    /**
     * Input için kullanılacak token değerini döndürür
     * @return bool|string
     */
    public function token()
    {
        return $this->session()->get('csrf_token');
    }


    /**
     * POST veya GET işlemlerinde CSRF yoksa false, varsa true döner
     * @return bool
     */
    public function check(): bool
    {
        if ($this->request()->request('csrf_token') == $this->token() && $this->isSelfReferer()) {
            return false;
        }

        return true;
    }

    /**
     * POST işlemlerinde CSRF yoksa false, varsa true döner
     * @return bool
     */
    public function checkPost(): bool
    {
        if ($this->request()->post('csrf_token') == $this->token() && $this->isSelfReferer()) {
            return false;
        }

        return true;
    }


    /**
     * GET işlemlerinde CSRF yoksa false, varsa true döner
     * @return bool
     */
    public function checkGet(): bool
    {
        if ($this->request()->get('csrf_token') == $this->token() && $this->isSelfReferer()) {
            return false;
        }

        return true;
    }


    /**
     * GET, POST işlemlerinde Cookie üzerinden kontrol eder CSRF yoksa false, varsa true döner
     * @return bool
     */
    public function checkCookie(): bool
    {
        if ($this->cookie()->get('csrf_token') == $this->token() && $this->isSelfReferer()) {
            if ($this->request()->server('cache-control') != 'max-age=0') {
                return false;
            }
        }

        return true;
    }


    /**
     * istek kaynağı kendi sitemiz ise true
     * @return bool
     */
    private function isSelfReferer(): bool
    {
        if (strpos($this->request()->referer(), $this->request()->host())) {
            return true;
        }

        return false;
    }

    /**
     * @return Session
     */
    private function session():Session
    {
        return $this->app->resolve(Session::class);
    }

    /**
     * @return Cookie
     */
    private function cookie():Cookie
    {
        return $this->app->resolve(Cookie::class);
    }

    /**
     * @return Request
     */
    private function request():Request
    {
        return $this->app->resolve(Request::class);
    }
}
