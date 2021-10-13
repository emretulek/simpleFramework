<?php

namespace Core\Http;

class Request
{
    /**
     * @var string
     */
    public string $basePath;

    /**
     * @var array
     */
    public array $post = [];

    /**
     * @var array
     */
    public array $get = [];

    /**
     * @var array
     */
    public array $request = [];

    /**
     * @var array
     */
    public array $files = [];

    /**
     * @var array
     */
    public array $cookie = [];

    /**
     * Request constructor.
     * @param string $basePath
     */
    public function __construct($basePath = '')
    {
        $this->basePath = $basePath;

        $this->get = isset($_GET) ? $_GET : [];
        $this->post = isset($_POST) ? $_POST : [];
        $this->request = isset($_REQUEST) ? $_REQUEST : [];
        $this->files = isset($_FILES) ? $_FILES : [];
        $this->cookie = isset($_COOKIE) ? $_COOKIE : [];
    }

    /**
     * @return string
     */
    public function path(): string
    {
        $request_uri = urldecode(filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_DEFAULT));
        $path = parse_url($request_uri, PHP_URL_PATH);
        $path = preg_replace('#/+#', '/', $path);
        $path = preg_replace('#\.+#', '.', $path);
        return (string) trim($path, '/');
    }

    /**
     * Yapılan isteği döndürür.
     * @return string
     */
    public function requestUri(): string
    {
        $path = preg_replace("@^" . trim($this->basePath, '/') . "/@i", '/', $this->path() . '/', 1);
        return trim($path, '/');
    }


    /**
     * Girilen string yada regex requestUri ile eşleşirse true aksi halde false döndürür
     * @param $uri
     * @return bool
     */
    public function matchUri($uri): bool
    {
        $uri = ltrim($uri, '/');
        return (bool)preg_match('#^' . $uri . '$#', $this->requestUri());
    }

    /**
     * Mevcut adres satırını döndürür.
     * @return string
     */
    public function currentUrl(): string
    {
        $queryString = $_SERVER['QUERY_STRING'] ? '?' . urldecode($_SERVER['QUERY_STRING']) : "";
        return trim($this->baseUrl(), '/') . '/' . $this->requestUri() . $queryString;
    }

    /**
     * Site adresini döndürür.
     * @return string
     */
    public function baseUrl(): string
    {
        return $this->scheme() . '://' . self::host() . rtrim($this->basePath, '/') . '/';
    }

    /**
     * İstek yapılan adresi dizin yapısına göre parçalar.
     *
     * @param int|null $key index girilirse değerini girilmezse tüm segmentleri döndürür.
     * @return mixed
     */
    public function segments(?int $key = null)
    {
        $queryString = preg_replace("#^(/index\.php)#i", "", $this->requestUri());
        $queryString = trim($queryString, "/");
        $segments = array_values(array_filter(explode("/", $queryString), function ($item) {
            return $item !== null && $item !== '';
        }));

        if (is_null($key)) {
            return $segments;
        }
        if (array_key_exists($key, $segments)) {
            return $segments[$key];
        }
        return null;
    }


    /**
     * Global $_REQUEST değişkenine erişim sağlar.
     *
     * @param string|null $name nokta ile birleşitirilmiş index (index1.index2) değeri alır,
     * belirtilmezse tüm diziyi döndürür. GET yoksa yada index yoksa false döner.
     * @return mixed
     */
    public function request(string $name = null)
    {
        if (is_null($name)) {
            return $this->request;
        }

        return dot_aray_get($this->request, $name);
    }


    /**
     * Global $_GET değişkenine erişim sağlar.
     *
     * @param string|null $name nokta ile birleşitirilmiş index (index1.index2) değeri alır,
     * belirtilmezse tüm diziyi döndürür. GET yoksa yada index yoksa false döner.
     * @return null|mixed
     */
    public function get(string $name = null)
    {
        array_walk_recursive($this->get, function (&$item) {
            $item = trim(strip_tags($item));
        });

        if (is_null($name)) {
            return $this->get;
        }

        return dot_aray_get($this->get, $name);
    }


    /**
     * Global $_POST değişkenine erişim sağlar.
     *
     * @param string|null $name nokta ile birleşitirilmiş index (index1.index2) değeri alır,
     * belirtilmezse tüm diziyi döndürür. POST yoksa yada index yoksa false döner.
     * @return null|mixed
     */
    public function post(string $name = null)
    {
        array_walk_recursive($this->post, function (&$item) {
            $item = trim($item);
        });

        if (is_null($name)) {
            return $this->post;
        }

        return dot_aray_get($this->post, $name);
    }


    /**
     * Global $_FILES değişkenine erişim sağlar. $_FILES[name][0] yapısını,
     * $_FILES[0][name] şeklinde değiştirir.
     *
     * @param string|null $name nokta ile birleşitirilmiş index (index1.index2) değeri alır,
     * belirtilmezse tüm diziyi döndürür. FILES yoksa yada index yoksa false döner.
     * @return array
     */
    public function files(string $name = null): array
    {
        $sort_files = [];

        // her resim için yeni dizi oluşturur
        foreach ($this->files as $input_name => $inputs) {
            if (is_array($inputs['name'])) {
                foreach ($inputs['name'] as $key => $file_name) {
                    $sort_files[$input_name][] = [
                        'name' => $file_name,
                        'type' => $inputs['type'][$key],
                        'tmp_name' => $inputs['tmp_name'][$key],
                        'error' => $inputs['error'][$key],
                        'size' => $inputs['size'][$key],
                    ];
                }
            } else {
                $sort_files[$input_name] = $inputs;
            }
        }

        if ($name) {
            return dot_aray_get($sort_files, $name) ?? [];
        }

        return $sort_files;
    }


    /**
     * @param string|null $name
     * @return mixed
     */
    public function cookie(string $name = null)
    {
        array_walk_recursive($this->cookie, function (&$item) {
            $item = trim(strip_tags($item));
        });

        if (is_null($name)) {
            return $this->cookie;
        }

        return dot_aray_get($this->cookie, $name);
    }

    /**
     * request raw data
     * @return string
     */
    public function raw(): string
    {
        if ($data = file_get_contents('php://input')) {
            return $data;
        }

        return "";
    }

    /**
     * İstek methodunu kontrol eder doğrusa true değilse false döner.
     * $method girilmezse header bilgisinden methodu döndürür.
     *
     * @param string|null $method kontrol edilecek method [POST, GET, PUT, PATCH, DELETE]
     * @return bool|string
     */
    public function method(string $method = null)
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
    public function isAjax(string $method = null): bool
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            if ($this->method($method)) {
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
    public function scheme(): string
    {
        return isset($_SERVER['HTTPS']) ? 'https' : 'http';
    }


    /**
     * Host adını döndürür
     *
     * @return string
     */
    public function host(): string
    {
        return $_SERVER['SERVER_NAME'] ?? '';
    }


    /**
     * İstek üst bilgisinde varsa dil anahtarını yoksa ön tanımlı dili anahtarını döndürür.
     *
     * @param bool $basic
     * @return string
     */
    public function local(bool $basic = false): string
    {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $local = preg_split("/[,;]/", $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            if (array_key_exists(0, $local) && preg_match('/[a-z-]/i', $local[0]) && $basic == false) {
                return $local[0];
            }
            $local = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            if (ctype_alpha($local)) {
                return $local;
            }
        }
        return '';
    }


    /**
     * Useragent bilgisini döndürür.
     * @return string
     */
    public function userAgent(): string
    {
        return $this->server('user-agent');
    }


    /**
     * Referer bilgisini döndürür.
     * @return string
     */
    public function referer(): string
    {
        $referer = $this->server('referer');

        if (filter_var($referer, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $referer;
        }
        if (filter_var($referer, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $referer;
        }
        if (filter_var($referer, FILTER_VALIDATE_URL)) {
            return $referer;
        }

        return '';
    }


    /**
     * IP adresini döndürür.
     *
     * @return string
     */
    public function ip(): string
    {
        return $this->server('remote-addr') ?? '127.0.0.1';
    }


    /**
     * Proxy ardında ki ip adresini döndürür, proxy bilgisi yoksa direk ip döndürür.
     *
     * @return string
     */
    public function forwardedIp(): string
    {
        $ip = $this->server('client-ip');

        if ($this->server('x-forwarded-for')) {
            $proxies = explode(',', $this->server('x-forwarded-for'));
            $ip = $proxies[0];
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $ip;
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $ip;
        }

        return $this->ip();
    }


    /**
     * $_SERVER değişkenlerini döndürür
     *
     * @param $value
     * @return mixed
     */
    public function server($value): string
    {
        $value = str_replace('-', '_', $value);
        $serverVariable = $_SERVER[strtoupper($value)] ?? $_SERVER['HTTP_' . strtoupper($value)] ?? null;
        return (string)filter_var($serverVariable, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    }
}

