<?php

use Core\App;
use Core\Config\Config;
use Core\Http\Request;
use Core\Http\Response;
use Core\Language\Language;
use Core\View\View;

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

        echo '<div style="padding: 5px; margin: 5px 15px; background-color: #eeeeee; border: solid 1px #cecece">' . PHP_EOL;

        foreach ($params as $key => $param) {

            echo '<pre style="background-color: #ffffff; padding: 10px; margin: 0;">' . PHP_EOL;
            echo '<h3 style="background-color: #cecece; margin: 0 0 10px 0; padding: 10px;">Dump <small>paramater(' . ($key + 1) . ')</small></h3>';
            echo '<div style="color:#a94442; padding: 10px;">';
            var_dump($param);
            echo '</div>' . PHP_EOL;
            echo '</pre>' . PHP_EOL;
        }
        echo '<div style="background-color: #cecece; padding: 5px">' . PHP_EOL;
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
     * @param string $separator
     */
    function dot_aray_set(array &$array, string $path, $value, $separator = '.')
    {
        $keys = explode($separator, $path);

        foreach ($keys as $key) {

            $array = &$array[$key];
        }
        $array = $value;
    }
}

if (!function_exists('dot_aray_get')) {
    /**
     * Bir dizinin (index1.index2.index) nokta ile birleştirilmiş indexine erişir.
     * Nokta ile birleştirilmiş dizi indexlari varsa değerini yoksa false döndürür.
     *
     * @param array $array Erişilecek dizi
     * @param string $path indexlerin noktalı birleşimi
     * @param string $separator
     * @return mixed
     */
    function dot_aray_get(array $array, string $path, $separator = '.')
    {
        $keys = explode($separator, $path);

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
     * @param string $separator
     * @return bool
     */
    function dot_array_del(array &$array, string $path, $separator = '.')
    {
        $keys = explode($separator, $path);

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

if (!function_exists('conf')) {
    /**
     * Config sınıfının hızlı erişim methodu
     * @param $key
     * @param mixed $value
     * @return mixed
     */
    function conf($key, $value = null)
    {
       if($value === null){
           return Config::get($key);
       }

       Config::set($key, $value);
       return true;
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
        echo Language::translate($key, $args);
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
    function counter(int &$count, int $step = 1, int $start = 1)
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
    function random(int $length, ?string $type = null)
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
    function baseUrl($path = null)
    {
        $path = $path ? trim($path, '/') : '';
        return Request::baseUrl() . $path;
    }
}


if (!function_exists('groupUrl')) {
    /**
     * @param null $path url adresine eklenecek bölüm
     * @return string
     */
    function groupUrl($path = null)
    {
        $path = $path ? trim($path, '/') : '';
        $group  = Router::getPrefix() ? Router::getPrefix().'/' : '';
        return Request::baseUrl() . $group . $path;
    }
}


if (!function_exists('assetsUrl')) {
    /**
     * Assets dizini altında dosya yolu
     *
     * @param null $path url adresine eklenecek bölüm
     * @return string
     */
    function assetsUrl($path = null)
    {
        return Request::baseUrl() . Config::get('path.assets') . '/' . $path;
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
    function url(string $path = null, array $params = array())
    {
        $parameters = $params ? '?' . http_build_query($params) : '';
        $path = trim($path, '/');

        //http veya https varsa dış bağlantı olarak değerlendir
        if(preg_match("#^https?://#", $path, $match)){
            return $path.$parameters;
        }

        //dil desteği aktifse url yapısını ona göre oluştur
        if (Language::getDefault()) {
            if (Language::getDefault()->key != Language::getActive()->key) {

                return Request::baseUrl() . Language::getActive()->key . '/' . $path . $parameters;
            }
        }

        return Request::baseUrl() . $path . $parameters;
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
    function redirect(string $url, $code = null)
    {
        if (!headers_sent()) {
            if(filter_var($url, FILTER_VALIDATE_URL)){
                App::getInstance(Response::class)->redirect($url, $code);
            }else{
                App::getInstance(Response::class)->redirect(url($url), $code);
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
    function page($fileName, $data = array())
    {
        $view = App::getInstance(View::class);
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
    function viewPath($fileName, $data = array(), string $ext = EXT)
    {
        $view = App::getInstance(View::class);
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
    function json($data)
    {
        $view = App::getInstance(View::class);
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
    function jsonSuccess($message = null, $location = null, $data = null)
    {
        $view = App::getInstance(View::class);
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
    function jsonError($message = null, $location = null, $data = null)
    {
        $view = App::getInstance(View::class);
        return $view->json(['status' => 'error', 'message' => $message, 'location' => $location, 'data' => $data]);
    }
}

if (!function_exists('template')) {
    /**
     * Core\View::template metodunun eş değeri
     *
     * @param $fileName
     * @param array $data
     * @return View
     */
    function template($fileName, $data = array())
    {
        $view = App::getInstance(View::class);
        return $view->template($fileName, $data);
    }
}

if (!function_exists('setTemplate')) {
    /**
     * Core\View::setTemplate metodunun eş değeri
     *
     * @param $template
     * @return View
     */
    function setTemplate($template)
    {
        $view = App::getInstance(View::class);
        return $view->setTemplate($template);
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
    function is_readable_file(string $filename)
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
    function is_writable_file(string $filename)
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
    function is_readable_dir(string $filename)
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
    function is_writable_dir(string $filename)
    {
        return is_dir($filename) && is_writable($filename);
    }
}
#endregion
