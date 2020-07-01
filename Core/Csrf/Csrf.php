<?php


namespace Core\Csrf;


use Core\Cookie\Cookie;
use Core\Http\Request;
use Core\Session\Session;

class Csrf
{
    /**
     * Csrf için token oluşturup cookie ve sessiona atar
     * $_SESSION old_csrf_token, csrf_token
     * $_COOKIE csrf_token
     */
    public static function generateToken()
    {
        $token = md5(uniqid());
        Session::set('old_csrf_token', Session::get('csrf_token'));
        Session::set('csrf_token', $token);
        Cookie::set('csrf_token', $token, 1, '/', null, false, false);
    }


    /**
     * Input için kullanılacak token değerini döndürür
     * @return bool|mixed
     */
    public static function token()
    {
        return Session::get('csrf_token');
    }

    /**
     * POST veya GET işlemlerinde CSRF yoksa false, varsa true döner
     * @return bool
     */
    public static function check()
    {
        if (Request::request('csrf_token') == Session::get('old_csrf_token') && strpos(Request::referer(), Request::host())) {
            return false;
        }

        return true;
    }

    /**
     * POST işlemlerinde CSRF yoksa false, varsa true döner
     * @return bool
     */
    public static function checkPost()
    {
        if(Request::method('post')) {
            if (Request::post('csrf_token') == Session::get('old_csrf_token') && strpos(Request::referer(), Request::host())) {
                return false;
            }
        }

        return true;
    }


    /**
     * GET işlemlerinde CSRF yoksa false, varsa true döner
     * @return bool
     */
    public static function checkGet()
    {
        if(Request::method('get')) {
            if (Request::get('csrf_token') == Session::get('old_csrf_token') && strpos(Request::referer(), Request::host())) {
                return false;
            }
        }

        return true;
    }


    /**
     * GET, POST işlemlerinde Cookie üzerinden kontrol eder CSRF yoksa false, varsa true döner
     */
    public static function checkCookie()
    {
        if(Cookie::get('csrf_token') == Session::get('old_csrf_token') && strpos(Request::referer(),Request::host())){
            if(Request::server('cache-control') != 'max-age=0') {
                return false;
            }
        }

        return true;
    }
}
