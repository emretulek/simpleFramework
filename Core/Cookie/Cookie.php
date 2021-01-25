<?php

namespace Core\Cookie;

use Core\Http\Request;

class Cookie
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

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
    public function set(string $name, string $value, $lifetime = "+1 Month", $path = "/", $domain = null, $secure = null, $http_only = true, $sameSite = 'strict'): bool
    {
        if ($secure == null) {
            $secure = $this->request->scheme() === 'https';
        }

        $options = [
            'expires' => $this->expires($lifetime),
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $http_only,
            'samesite' => mb_convert_case($sameSite, MB_CASE_TITLE)
        ];

        return setcookie($this->arrayCookieName($name), $value, $options);
    }

    /**
     * Adı girilen cookie değerini döndürür.
     *
     * @param string|null $name nokta ile birleşitirilmiş cookie indexi (index1.index2)
     * @return mixed
     */
    public function get(string $name = null)
    {
        return $this->request->cookie($name);
    }

    /**
     * Adı girilen cookie değerini siler.
     *
     * @param mixed $name nokta ile birleşitirilmiş cookie indexi (index1.index2)
     * @return bool
     */
    public function remove(string $name): bool
    {
        return $this->set($name, "", -1);
    }


    /**
     * Domain ve subdomain altındaki tüm cookileri siler.
     */
    public function destroy(): bool
    {
        $stats = [];

        if ($this->request->server('cookie')) {
            $cookies = explode(';', $this->request->server('cookie'));
            foreach ($cookies as $cookie) {
                $parts = explode('=', $cookie);
                $name = trim($parts[0]);
                $stats[] = setcookie($name, '', time() - 1000);
                $stats[] = setcookie($name, '', time() - 1000, '/');
            }
        }

        return !in_array(false, $stats);
    }


    /**
     * Dizi şeklinde cookie isimleri oluşturmak için kullanılır.
     * Örnek: name[index][index2]
     *
     * @param string $name nokta ile birleşitirilmiş cookie indexi (index1.index2)
     * @return string
     */
    private function arrayCookieName(string $name): string
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
    private function expires($time)
    {
        if (is_numeric($time)) {
            return time() + $time;
        }

        return strtotime($time);
    }
}
