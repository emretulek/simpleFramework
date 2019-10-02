<?php

namespace Core\Cookie;
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
     * @param bool $secure
     * @param bool $http_only
     * @return bool
     */
    public static function set(string $name, string $value, $lifetime = 24, $path = "/", $domain = null, $secure = false, $http_only = true)
    {
        return setcookie(self::arrayCookieName($name), $value, self::expires($lifetime), $path, $domain, $secure, $http_only);
    }

    /**
     * Adı girilen cookie değerini döndürür.
     *
     * @param string $name nokta ile birleşitirilmiş cookie indexi (index1.index2)
     * @return bool|mixed
     */
    public static function get(string $name)
    {
        return dot_aray_get($_COOKIE, $name);
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
     * @param string|int $time (int saat) (string 1d = 1gün, 1i = 1dk) php date() fonksiyonuna benzer çalışır.
     * @return float|int|string
     */
    private static function expires($time)
    {
        if ($integer_time = mb_stristr("s", $time)) {
            $expires = $integer_time;
        } elseif ($integer_time = mb_stristr("i", $time)) {
            $expires = $integer_time * 60;
        } elseif ($integer_time = mb_stristr("h", $time)) {
            $expires = $integer_time * 60 * 60;
        } elseif ($integer_time = mb_stristr("d", $time)) {
            $expires = $integer_time * 60 * 60 * 24;
        } elseif ($integer_time = mb_stristr("m", $time)) {
            $expires = $integer_time * 60 * 60 * 24 * 30;
        } elseif ($integer_time = mb_stristr("y", $time)) {
            $expires = $integer_time * 60 * 60 * 24 * 30 * 12;
        } else {
            $expires = intval($time) * 60 * 60;
        }

        return time() + $expires;
    }
}
