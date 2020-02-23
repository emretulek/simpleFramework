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
     * Csrf tokenlerini kontrol eder
     * @return bool
     */
    public static function check()
    {
        // post token kontrolü
        if(Request::post('csrf_token') == Session::get('old_csrf_token') && strpos(Request::referer(),Request::host())){
            Session::tmpSet('csrf', true);
            return true;
        }

        // get token kontrolü
        if(Request::get('csrf_token') == Session::get('old_csrf_token') && strpos(Request::referer(),Request::host())){
            Session::tmpSet('csrf', true);
            return true;
        }

        //cookie token kontrolü
        if(Cookie::get('csrf_token') == Session::get('old_csrf_token') && strpos(Request::referer(),Request::host())){
            Session::tmpSet('csrf', true);
            return true;
        }
        return false;
    }
}
