<?php

namespace Core\Http;

/**
 * Class Request
 * Sunucuya yapılan isteklere erişim sağlar
 */
class Request
{

    /**
     * @return string
     */
    public static function path()
    {
        return parse_url(filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_DEFAULT), PHP_URL_PATH);
    }


    /**
     * Mevcut adres satırını döndürür.
     * @return string
     */
    public static function url()
    {
        return trim(self::baseUrl(), '/') . self::path();
    }

    /**
     * Site adresini döndürür.
     * @return string
     */
    public static function baseUrl()
    {
        return self::scheme() . '://' . $_SERVER['SERVER_NAME'] . '/';
    }

    /**
     * İstek yapılan adresi dizin yapısına göre parçalar.
     *
     * @param int|null $key index girilirse değerini girilmezse tüm segmentleri döndürür.
     * @return mixed
     */
    public static function segments(?int $key = null)
    {
        $queryString = parse_url(self::path(), PHP_URL_PATH);
        $queryString = preg_replace("#^(/index\.php)#i", "", $queryString);
        $queryString = trim($queryString, "/");
        $segments = array_values(array_filter(explode("/", $queryString)));

        if (is_null($key)) {
            return $segments;
        }
        if (array_key_exists($key, $segments)) {
            return $segments[$key];
        }
        return false;
    }


    /**
     * Global $_GET değişkenine erişim sağlar.
     *
     * @param string|null $name nokta ile birleşitirilmiş index (index1.index2) değeri alır,
     * belirtilmezse tüm diziyi döndürür. GET yoksa yada index yoksa false döner.
     * @return null
     */
    public static function get(string $name = null)
    {
        if (is_null($name)) return $_GET;
        return dot_aray_get($_GET, $name);
    }


    /**
     * Global $_POST değişkenine erişim sağlar.
     *
     * @param string|null $name nokta ile birleşitirilmiş index (index1.index2) değeri alır,
     * belirtilmezse tüm diziyi döndürür. POST yoksa yada index yoksa false döner.
     * @return null
     */
    public static function post(string $name = null)
    {
        if (is_null($name)) return $_POST;
        return dot_aray_get($_POST, $name);
    }


    /**
     * Global $_FILES değişkenine erişim sağlar. $_FILES[name][0], $_FILES[name][1] yapısını,
     * $_FILES[0][name] şeklinde değiştirir.
     *
     * @param string|null $name nokta ile birleşitirilmiş index (index1.index2) değeri alır,
     * belirtilmezse tüm diziyi döndürür. FILES yoksa yada index yoksa false döner.
     * @return null
     */
    public static function files(string $name = null)
    {
        if (!isset($_FILES)) return false;
        if (is_null($name)) return $_FILES;
        $files = dot_aray_get($_FILES, $name);
        $sort_files = [];

        if (isset($files['name']) && is_array($files['name'])) {
            foreach ($files['name'] as $name => $file_name) {
                $sort_files[$name]['name'] = $file_name;
                $sort_files[$name]['type'] = $files['type'][$name];
                $sort_files[$name]['tmp_name'] = $files['tmp_name'][$name];;
                $sort_files[$name]['error'] = $files['error'][$name];;
                $sort_files[$name]['size'] = $files['size'][$name];;
            }
            return $sort_files;
        }
        return $files;
    }


    /**
     * İstek methodunu kontrol eder doğrusa true değilse false döner.
     *
     * @param string $method kontrol edilecek method [POST, GET, PUT, PATCH, DELETE]
     * @return bool
     */
    public static function method(string $method = null)
    {
        if (is_null($method)) {
            return $_SERVER['REQUEST_METHOD'];
        }
        if ($_SERVER['REQUEST_METHOD'] == strtoupper($method)) {
            return true;
        }
        return false;
    }


    /**
     * İstek bilgisinde xmlhttprequest var mı kontrol eder.
     * @param $method = 'get'
     * @return bool
     */
    public static function isAjax(string $method = null)
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            if (self::method($method)) {
                return true;
            }
        }
        return false;
    }


    /**
     * İstek protokolünü döndürür htpp|https
     *
     * @return string
     */
    public static function scheme()
    {
        return isset($_SERVER['HTTPS']) ? 'https' : 'http';
    }


    /**
     * Host adını döndürür
     *
     * @return string
     */
    public static function host()
    {
        return $_SERVER['SERVER_NAME'] ?? null;
    }


    /**
     * İstek üst bilgisinde varsa dil anahtarını yoksa ön tanımlı dili anahtarını döndürür.
     *
     * @return bool|null|string
     */
    public static function local()
    {
        return isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
    }


    /**
     * Useragen bilgisini döndürür.
     *
     * @return mixed
     */
    public static function userAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? null;
    }

    /**
     * IP adresini döndürür.
     *
     * @return mixed
     */
    public static function ip()
    {
        return $_SERVER['REMOTE_ADDR'];
    }


    /**
     * Proxy ardında ki ip adresini döndürür, proxy bilgisi yoksa direk ip döndürür.
     *
     * @return mixed
     */
    public static function forwardedIp()
    {
        $ip = null;

        if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }

        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
            $proxies = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = $proxies[0];
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $ip;
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $ip;
        }

        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * $_SERVER değişkenlerini döndürür
     *
     * @param $value
     * @return mixed|null
     */
    public static function server($value)
    {
        $value = strtoupper($value);

        if(isset($_SERVER[$value])){
            return $_SERVER[$value];
        }elseif(isset($_SERVER['HTTP_'.$value])){
            return $_SERVER['HTTP_'.$value];
        }else{
            return null;
        }
    }
}

