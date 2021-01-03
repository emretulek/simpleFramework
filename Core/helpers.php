<?php

use Core\App;
use Core\Config\Config;
use Core\Http\Request;
use Core\Http\Response;
use Core\Language\Language;
use Core\View\View;

if (!function_exists('app')) {
    /**
     * @return App
     */
    function app():App
    {
        return App::getInstance();
    }
}

if (!function_exists('debug')) {
    /**
     * @param Throwable $throwable
     */
    function debug(Throwable $throwable):void
    {
        app()->debug($throwable);
    }
}

if (!function_exists('lang')) {
    /**
     * @return Language
     */
    function lang():Language
    {
        return app()->resolve(Language::class);
    }
}

if (!function_exists('console_log')) {
    /**
     * Girilen mesajı javascript konsolunda gönderir.
     *
     * @param mixed $message
     */
    function console_log(...$message)
    {
        register_shutdown_function(function () use ($message){
            echo '<script>';
            echo 'console.log(' . json_encode($message) . ')';
            echo '</script>';
        });
    }
}

if (!function_exists('dump')) {
    /**
     * Girilen değerleri var_dump kullanarak okunabilir şekilde çıktılar.
     *
     * @param mixed ...$params
     */
    function dump(...$params)
    {
        $trace = debug_backtrace();
        echo '<style>';
        echo 'body{background-color:#292929}
                .debug-container{margin: 15px;background-color: #000000;color: #f9a825;padding: 5px;font-family: Arial, sans-serif;}
                .debug-title{margin: 0;padding: 10px;background-color: #1f1f1f;color: #bb86fc;}
                .debug-message {padding: 15px;}
                .debug-footer {background-color: #352c2d;padding: 10px;color: #ff7597;}';
        echo '</style>';
        echo '<div class="debug-container">' . PHP_EOL;

        foreach ($params as $key => $param) {

            echo '<h3 class="debug-title">Dump <small>paramater(' . ($key + 1) . ')</small></h3>';
            echo '<pre>' . PHP_EOL;
            echo '<div class="debug-message">';
            var_dump($param);
            echo '</div>' . PHP_EOL;
            echo '</pre>' . PHP_EOL;
        }
        echo '<div class="debug-footer">' . PHP_EOL;
        echo ' <b>File:</b> ' . $trace[0]['file'];
        echo ' <b>Line:</b> ' . $trace[0]['line'];
        echo '</div>';
        echo '</div>';
    }
}

if (!function_exists('dot_aray_set')) {
    /**
     * Bir dizinin (index1.index2.index) nokta ile birleştirilmiş indexine erişir.
     * Nokta ile birleştirilmiş dizi indexlari varsa yeni değer atar, yoksa oluşturur.
     *
     * @param array $array Erişilecek dizi
     * @param string $path indexlerin noktalı birleşimi
     * @param mixed $value atancak değer
     */
    function dot_aray_set(array &$array, string $path, $value)
    {
        $keys = explode('.', $path);

        foreach ($keys as $key) {

            $array = &$array[$key];
        }
        $array = $value;
    }
}

if (!function_exists('dot_aray_get')) {
    /**
     * @param array $array
     * @param string $path
     * @return mixed|null|array
     */
    function dot_aray_get(array $array, string $path)
    {
        $keys = explode('.', $path);

        foreach ($keys as $key) {

            if (is_array($array) && array_key_exists($key, $array)) {
                $array = $array[$key];
            } else {
                return null;
            }
        }
        return $array;
    }
}

if (!function_exists('dot_array_del')) {
    /**
     * Bir dizinin (index1.index2.index) nokta ile birleştirilmiş indexine erişir.
     * Nokta ile birleştirilmiş dizi indexlari varsa değerini siler true döndürür yoksa false döndürür.
     *
     * @param array $array Erişilecek dizi
     * @param string $path indexlerin noktalı birleşimi
     * @return bool
     */
    function dot_array_del(array &$array, string $path):bool
    {
        $keys = explode('.', $path);

        foreach ($keys as $key) {

            if (is_array($array) && array_key_exists($key, $array)) {
                $array = &$array[$key];
            } else {
                return false;
            }
        }

        $array = null;
        return true;
    }
}

if (!function_exists('config')) {
    /**
     * Config sınıfının hızlı erişim methodu
     * @param $key
     * @param mixed $value
     * @return mixed
     */
    function config($key, $value = null)
    {
       if($value === null){
           return app()->resolve(Config::class)->get($key);
       }

       return app()->resolve(Config::class)->set($key, $value);
    }
}

if (!function_exists('__')) {
    /**
     * Akitf dildeki çeviriyi yazdırır, aktif dilde yoksa default dil, default yoksa boş döner
     * @param $key
     * @param mixed ...$args
     */
    function __($key, ...$args)
    {
        echo lang()->translate($key, $args);
    }
}

if (!function_exists('counter')) {
    /**
     * Özellikle döngü sıra numaralrında kullanılmak üzere her kullanılışta
     * başlangıç sayısnı belirtilen miktarda arttırır.
     *
     * @param int $count
     * @param int $step
     * @param int $start
     * @return int
     */
    function counter(int &$count, int $step = 1, int $start = 1):int
    {
        $count = is_null($count) ? $start : $count + $step;
        return $count;
    }
}

if (!function_exists('random')) {

    /**
     * Random dizge oluşturur
     * @param int $length
     * @param ?string $type [number, alpha, special, alnum]
     * @return string|null
     */
    function random(int $length, ?string $type = null):string
    {
        $random = null;
        $characters = null;

        $number = "0123456789";
        $alpha = "abcdefghijklmnopqrstuvwxyzABCDEFGIJKLMNOPQRSTUVWXYZ";
        $special = "!^+$%&/()[]=}?*_@";

        switch ($type){
            case 'number': $characters = $number; break;
            case 'alpha' : $characters = $alpha; break;
            case 'special' : $characters = $special; break;
            case 'alnum' : $characters = $number.$alpha; break;
            default: $characters = $number.$alpha.$special;
        }

        $characterSize = strlen($characters);

        for($i = 0; $i < $length; $i++){
            $random .= $characters[rand(0,$characterSize-1)];
        }

        return $random;
    }
}

#region URL ile ilgili fonksiyonlar
if (!function_exists('baseUrl')) {
    /**
     * @param null $path url adresine eklenecek bölüm
     * @return string
     */
    function baseUrl($path = null):string
    {
        $path = $path ? trim($path, '/') : '';
        return app()->resolve(Request::class)->baseUrl() . $path;
    }
}


if (!function_exists('groupUrl')) {
    /**
     * @param null $path url adresine eklenecek bölüm
     * @return string
     */
    function groupUrl($path = null):string
    {
        $path = $path ? trim($path, '/') : '';
        $group  = app()->resolve(Router::class)->getPrefix ?
            app()->resolve(Router::class)->getPrefix : '';
        return baseUrl($group . $path);
    }
}


if (!function_exists('assetsUrl')) {
    /**
     * Assets dizini altında dosya yolu
     *
     * @param null $path url adresine eklenecek bölüm
     * @return string
     */
    function assetsUrl($path = null):string
    {
        return baseUrl(app()->config['path']['assets']) . '/' . $path;
    }
}


if (!function_exists('src')) {
    /**
     * Assets dizini altında dosya yolu
     *
     * @param null $path url adresine eklenecek bölüm
     */
    function src($path = null)
    {
        echo assetsUrl($path);
    }
}


if (!function_exists('url')) {

    /**
     * Çoklu dil desteğini dikkate alarak url oluşturur.
     *
     * @param string|null $path url adresi (dil anahtarı olmadan)
     * @param array $params varsa get parametreleri
     * @return string
     */
    function url(string $path = null, array $params = array()):string
    {
        $parameters = $params ? '?' . http_build_query($params) : '';
        $path = trim($path, '/');

        //http veya https varsa dış bağlantı olarak değerlendir
        if(preg_match("#^https?://#", $path, $match)){
            return $path.$parameters;
        }

        //dil desteği aktifse url yapısını ona göre oluştur
        if (Language::$isLoaded) {
            if(lang()->getDefault()['key'] != lang()->getKey()) {
                return baseUrl(lang()->getKey() . '/' . $path . $parameters);
            }
        }

        return baseUrl($path . $parameters);
    }
}


if (!function_exists('href')) {

    /**
     * Çoklu dil desteğini dikkate alarak url oluşturur.
     *
     * @param string|null $path url adresi (dil anahtarı olmadan)
     * @param array $params varsa get parametreleri
     */
    function href(string $path = null, array $params = array())
    {
        echo url($path, $params);
    }
}


if (!function_exists('redirect')) {
    /**
     * Sayfa yönlendirmeleri için kullanılır.
     *
     * @param string $url yönlendirilecek sayfa adresi
     * @param null|int $code http header durum kodu default 302
     */
    function redirect(string $url, $code = 200)
    {
        if (!headers_sent()) {
            if(filter_var($url, FILTER_VALIDATE_URL)){
                (new Response())->redirect($url, $code);
            }else{
                (new Response())->redirect(url($url), $code);
            }
        }else {
            echo '<meta http-equiv = "refresh" content = "0; url = ' . url($url) . '" />';
            echo 'Sayfa yönlendirilemiyor lütfen <a href="' . url($url) . '">Buraya tıklayın.</a>';
        }

        exit;
    }
}
#endregion

#region View Helpers
if (!function_exists('page')) {
    /**
     * Core\View::page metodunun eş değeri
     *
     * @param $fileName
     * @param array $data
     * @return View
     */
    function page($fileName, $data = array()):View
    {
        $view = app()->resolve(View::class);
        return $view->page($fileName, $data);
    }
}

if (!function_exists('viewPath')) {
    /**
     * Core\View::part metodunun eş değeri
     *
     * @param $fileName
     * @param array $data
     * @param string $ext
     * @return View
     */
    function viewPath($fileName, $data = array(), string $ext = EXT):View
    {
        $view = app()->resolve(View::class);
        return $view->path($fileName, $data, $ext);
    }
}

if (!function_exists('json')) {
    /**
     * Core\View::json metodunun eş değeri
     *
     * @param $data
     * @return View
     */
    function json($data):View
    {
        $view = app()->resolve(View::class);
        return $view->json($data);
    }
}


if (!function_exists('jsonSuccess')) {
    /**
     * Json verisini success olarak render eder
     *
     * @param null $message
     * @param null $location
     * @param null $data
     * @return View
     */
    function jsonSuccess($message = null, $location = null, $data = null):View
    {
        $view = app()->resolve(View::class);
        return $view->json(['status' => 'success', 'message' => $message, 'location' => $location, 'data' => $data]);
    }
}

if (!function_exists('jsonError')) {
    /**
     * Json verisini error olarak render eder
     *
     * @param null $message
     * @param null $location
     * @param null $data
     * @return View
     */
    function jsonError($message = null, $location = null, $data = null):View
    {
        $view = app()->resolve(View::class);
        return $view->json(['status' => 'error', 'message' => $message, 'location' => $location, 'data' => $data]);
    }
}

if (!function_exists('layout')) {
    /**
     * Core\View::layout metodunun eş değeri
     *
     * @param $fileName
     * @param array $data
     * @return View
     */
    function layout($fileName, $data = array()):View
    {
        $view = app()->resolve(View::class);
        return $view->layout($fileName, $data);
    }
}

if (!function_exists('setLayout')) {
    /**
     * Core\View::setLayout metodunun eş değeri
     *
     * @param $layout
     * @return View
     */
    function setLayout($layout):View
    {
        $view = app()->resolve(View::class);
        return $view->setLayout($layout);
    }
}
#endregion

#region File (dosya) fonksiyonları
if (!function_exists('is_readable_file')) {
    /**
     * Dosya varsa ve okunabilirse true döndürür
     *
     * @param string $filename
     * @return bool
     */
    function is_readable_file(string $filename):bool
    {
       return is_file($filename) && is_readable($filename);
    }
}


if (!function_exists('is_writable_file')) {
    /**
     * Dosya varsa ve yazılabilirse true döndürür aksi halde exception fırlatır
     *
     * @param string $filename
     * @return bool
     */
    function is_writable_file(string $filename):bool
    {
        return is_file($filename) && is_writable($filename);
    }
}


if (!function_exists('is_readable_dir')) {
    /**
     * Dizin varsa ve okunabilirse true döndürür
     *
     * @param string $filename
     * @return bool
     */
    function is_readable_dir(string $filename):bool
    {
        return is_dir($filename) && is_readable($filename);
    }
}


if (!function_exists('is_writable_dir')) {
    /**
     * Dizin varsa ve yazılabilirse true döndürür
     *
     * @param string $filename
     * @return bool
     */
    function is_writable_dir(string $filename):bool
    {
        return is_dir($filename) && is_writable($filename);
    }
}
#endregion
