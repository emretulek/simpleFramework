<?php

namespace Core\Cookie;
use Core\Http\Request;

/**
 * Class Cookie
 * Kolay cookie yönetimi sağlar.
 */
class Cookie
{

    /**
     * Cookie oluşturur.
     *
     * @param string $name nokta ile birleşitirilmiş cookie indexi (index1.index2)
     * @param string $value
     * @param int|string $lifetime integer hours or  string (60s, 30i, 24h, 30d, 12m, 1y)
     * @param string $path
     * @param null $domain
     * @param bool|null $secure true sadece https, false http, null auto
     * @param bool $http_only
     * @param string $sameSite Strict, Lax, None
     * @return bool
     */
    public static function set(string $name, string $value, $lifetime = "+1 Day", $path = "/", $domain = null, $secure = null, $http_only = true, $sameSite = 'strict')
    {
        if($secure == null){
            $secure = Request::scheme() === 'https';
        }

        $options = [
            'expires' => self::expires($lifetime),
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $http_only,
            'samesite' => mb_convert_case($sameSite, MB_CASE_TITLE)
        ];

        return setcookie(self::arrayCookieName($name), $value, $options);
    }

    /**
     * Adı girilen cookie değerini döndürür.
     *
     * @param string $name nokta ile birleşitirilmiş cookie indexi (index1.index2)
     * @return bool|mixed
     */
    public static function get(string $name)
    {
        $cookie = array_map("strip_tags", $_COOKIE);
        $cookie = array_map("trim", $cookie);
        return dot_aray_get($cookie, $name);
    }

    /**
     * Adı girilen cookie değerini siler.
     *
     * @param mixed $name nokta ile birleşitirilmiş cookie indexi (index1.index2)
     * @return bool
     */
    public static function remove(string $name)
    {
        return self::set($name, "", -1);
    }


    /**
     * Domain ve subdomain altındaki tüm cookileri siler.
     */
    public static function destroy()
    {
        if (isset($_SERVER['HTTP_COOKIE'])) {
            $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
            foreach ($cookies as $cookie) {
                $parts = explode('=', $cookie);
                $name = trim($parts[0]);
                setcookie($name, '', time() - 1000);
                setcookie($name, '', time() - 1000, '/');
            }
        }
    }


    /**
     * Dizi şeklinde cookie isimleri oluşturmak için kullanılır.
     * Örnek: name[index][index2]
     *
     * @param string $name nokta ile birleşitirilmiş cookie indexi (index1.index2)
     * @return string
     */
    private static function arrayCookieName(string $name)
    {
        $keys = explode(".", $name);
        $name = array_shift($keys);
        $keys = array_map(function ($item) {
            return '[' . $item . ']';
        }, $keys);

        return $name . implode("", $keys);
    }


    /**
     * Cookie için bitiş tarihi oluşturur.
     *
     * @param string|int $time (int saniye) veya php strtotime
     * @return float|int|string
     */
    private static function expires($time)
    {
        if(is_numeric($time)){
            return time() + $time;
        }

        return strtotime($time);
    }
}
