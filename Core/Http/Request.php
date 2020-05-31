<?php

namespace Core\Http;

use Core\Config\Config;

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
        $request_uri = urldecode(filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_DEFAULT));
        return parse_url(trim($request_uri, '/'), PHP_URL_PATH);
    }

    /**
     * Yapılan isteği döndürür.
     * @return string|string[]|null
     */
    public static function requestUri()
    {
        $path = str_replace(Config::get('app.path'), '/', self::path());
        return rtrim(preg_replace("#/+#", "/", $path), '/');
    }


    /**
     * Girilen string yada regex requestUri ile eşleşirse true aksi halde false döndürür
     * @param $uri
     * @return false
     */
    public static function matchUri($uri)
    {
        $uri = trim($uri, '/');
        return (bool) preg_match('#^'.$uri.'$#', self::requestUri());
    }

    /**
     * Mevcut adres satırını döndürür.
     * @return string
     */
    public static function currentUrl()
    {
        $queryString = $_SERVER['QUERY_STRING'] ? '?'.urldecode($_SERVER['QUERY_STRING']) : "";
        return trim(self::baseUrl(), '/') . '/' . self::requestUri() . $queryString;
    }

    /**
     * Site adresini döndürür.
     * @return string
     */
    public static function baseUrl()
    {
        return self::scheme() . '://' .self::host() . rtrim(Config::get('app.path'), '/') . '/';
    }

    /**
     * İstek yapılan adresi dizin yapısına göre parçalar.
     *
     * @param int|null $key index girilirse değerini girilmezse tüm segmentleri döndürür.
     * @return mixed
     */
    public static function segments(?int $key = null)
    {
        $queryString = preg_replace("#^(/index\.php)#i", "", self::requestUri());
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
     * Global $_REQUEST değişkenine erişim sağlar.
     *
     * @param string|null $name nokta ile birleşitirilmiş index (index1.index2) değeri alır,
     * belirtilmezse tüm diziyi döndürür. GET yoksa yada index yoksa false döner.
     * @return null|mixed
     */
    public static function request(string $name = null)
    {
        if (!isset($_REQUEST)) return [];
        if (is_null($name)) return $_REQUEST;
        return dot_aray_get($_REQUEST, $name);
    }


    /**
     * Global $_GET değişkenine erişim sağlar.
     *
     * @param string|null $name nokta ile birleşitirilmiş index (index1.index2) değeri alır,
     * belirtilmezse tüm diziyi döndürür. GET yoksa yada index yoksa false döner.
     * @return null|mixed
     */
    public static function get(string $name = null)
    {
        if(empty($_GET)){
            return [];
        }

        $get = array_map("strip_tags", $_GET);
        $get = array_map("trim", $get);

        if (is_null($name)) return $get;
        return dot_aray_get($get, $name);
    }


    /**
     * Global $_POST değişkenine erişim sağlar.
     *
     * @param string|null $name nokta ile birleşitirilmiş index (index1.index2) değeri alır,
     * belirtilmezse tüm diziyi döndürür. POST yoksa yada index yoksa false döner.
     * @return null|mixed
     */
    public static function post(string $name = null)
    {
        if(empty($_POST)){
            return [];
        }

        $post = array_map("trim", $_POST);

        if (is_null($name)) return $post;
        return dot_aray_get($post, $name);
    }


    /**
     * Global $_FILES değişkenine erişim sağlar. $_FILES[name][0] yapısını,
     * $_FILES[0][name] şeklinde değiştirir.
     *
     * @param string|null $name nokta ile birleşitirilmiş index (index1.index2) değeri alır,
     * belirtilmezse tüm diziyi döndürür. FILES yoksa yada index yoksa false döner.
     * @return array
     */
    public static function files(string $name = null)
    {
        $sort_files = [];

        if(empty($_FILES)){
            return [];
        }

        // her resim için yeni dizi oluşturur
        foreach ($_FILES as $input_name => $inputs){
            if(is_array($inputs['name'])) {
                foreach ($inputs['name'] as $key => $file_name) {
                    $sort_files[$input_name][] = [
                        'name' => $file_name,
                        'type' => $inputs['type'][$key],
                        'tmp_name' => $inputs['tmp_name'][$key],
                        'error' => $inputs['error'][$key],
                        'size' => $inputs['size'][$key],
                    ];
                }
            }else{
                $sort_files[$input_name][] = $inputs;
            }
        }

        if($name){
            return dot_aray_get($sort_files, $name);
        }

        return $sort_files;
    }


    /**
     * request raw data
     * @return false|string|null
     */
    public static function raw()
    {
        if($data = file_get_contents('php://input')){
            return $data;
        }

        return null;
    }

    /**
     * İstek methodunu kontrol eder doğrusa true değilse false döner.
     * $method girilmezse header bilgisinden methodu döndürür.
     *
     * @param string $method kontrol edilecek method [POST, GET, PUT, PATCH, DELETE]
     * @return bool
     */
    public static function method(string $method = null)
    {
        if (is_null($method)) {
            return $_SERVER['REQUEST_METHOD'] ?? 'GET';
        }
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') == strtoupper($method)) {
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
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $local = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            if (ctype_alpha($local)) {
                return $local;
            }
        }
        return null;
    }


    /**
     * Useragen bilgisini döndürür.
     *
     * @return null|mixed
     */
    public static function userAgent()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        if ($userAgent) {
            return filter_var($userAgent, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }

        return null;
    }


    /**
     * Referer bilgisini döndürür.
     *
     * @return mixed|null
     */
    public static function referer()
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? null;

        if (filter_var($referer, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $referer;
        }
        if (filter_var($referer, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $referer;
        }
        if (filter_var($referer, FILTER_VALIDATE_URL)) {
            return $referer;
        }

        return null;
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
     * @return mixed
     */
    public static function server($value)
    {
        $value = str_replace('-', '_', $value);
        $serverVariable = $_SERVER[strtoupper($value)] ?? $_SERVER['HTTP_' . strtoupper($value)] ?? null;
        return filter_var($serverVariable, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    }
}

