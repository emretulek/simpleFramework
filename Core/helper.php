<?php

use Core\Config\Config;
use Core\Http\Request;
use Core\Http\Response;
use Core\Language\Language;
use Core\Log\LogException;

if (!function_exists('error_handler')) {

    /**
     * Ölümcül hatalar dışında tüm hataları yakalar.
     * Yakalanan hatalar LogExcepiton sınıfına gönderir.
     *
     * @param int $code hata kodu.
     * @param string $message hata mesajı.
     * @param string $file hatanın oluştuğu dosya.
     * @param int $line hatanın oluştuğu satır numarası.
     */
    function error_handler(int $code, string $message, string $file, int $line)
    {
        try {
            error_clear_last();
            throw new LogException($message, $code, null, $file, $line);
        } catch (LogException $exception) {
            $exception->debug();
        }
    }

    set_error_handler("error_handler");
}


if (!function_exists('fatal_error_handler')) {
    /**
     * Ölümcül hataları yakalar.
     * Yakalanan hatalar LogException sınıfına gönderilir.
     */
    function fatal_error_handler()
    {
        try {
            if ($last_error = error_get_last()) {
                throw new LogException($last_error['message'], $last_error['type'], null, $last_error['file'], $last_error['line']);
            }
        } catch (LogException $exception) {
            $exception->debug();
        }
    }

    error_reporting(0);
    register_shutdown_function("fatal_error_handler");
}


if (!function_exists('console_log')) {
    /**
     * Girilen mesajı javascript konsoluna gönderir.
     *
     * @param mixed $message
     */
    function console_log(...$message)
    {
        echo '<script>';
        echo 'console.log(' . json_encode($message) . ')';
        echo '</script>';
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
        echo '<div style="padding: 5px; margin: 5px 15px; background-color: #eeeeee">' . PHP_EOL;

        foreach ($params as $key => $param) {

            echo '<pre style="background-color: #ffffff; padding: 10px; margin: 10px;">' . PHP_EOL;
            echo '<h3 style="background-color: #cecece; margin: 0 0 10px 0; padding: 10px;">Dump <small>paramater(' . ($key + 1) . ')</small></h3>';
            echo '<div style="color:#a94442; padding: 10px;">';
            var_dump($param);
            echo '</div>' . PHP_EOL;
            echo '</pre>' . PHP_EOL;
        }
        echo '<div style="background-color: #CCCCCC; padding: 5px">' . PHP_EOL;
        echo ' <b>File:</b> ' . debug_backtrace()[0]['file'];
        echo ' <b>Line:</b> ' . debug_backtrace()[0]['line'];
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
    function dot_aray_get($array, $path, $separator = '.')
    {
        $keys = explode($separator, $path);

        foreach ($keys as $key) {

            if (is_array($array) && array_key_exists($key, $array)) {
                $array = $array[$key];
            } else {
                return false;
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
    function dot_array_del(&$array, $path, $separator = '.')
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
    function counter(&$count, $step = 1, $start = 1)
    {
        $count = is_null($count) ? $start : $count + $step;
        return $count;
    }
}


/**
 *
 * URL ile ilgili fonksiyonlar
 *
 */

if (!function_exists('baseUrl')) {
    /**
     * @param null $path url adresine eklenecek bölüm
     * @return string
     */
    function baseUrl($path = null)
    {
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

        if (class_exists(Language::class)) {
            if (Config::get('app.language') != Language::get()) {
                return Request::baseUrl() . Language::get() . '/' . $path . $parameters;
            }
            return Request::baseUrl() . $path . $parameters;
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
            (new Response())->redirect(url($url), $code);
        }else {
            echo '<meta http-equiv = "refresh" content = "0; url = ' . url($url) . '" />';
            echo 'Sayfa yönlendirilemiyor lütfen <a href="' . url($url) . '">Buraya tıklayın.</a>';
        }

        exit;
    }
}


//if (!function_exists('is_readable_file')) {
//    /**
//     * Dosya varsa ve okunabilirse true döndürür aksi halde exception fırlatır
//     *
//     * @param string $filename
//     * @return bool
//     * @throws Exception
//     */
//    function is_readable_file(string $filename)
//    {
//       if(is_file($filename)){
//           if(is_readable($filename)){
//
//               return true;
//           }
//           throw new Exception($filename. ' dosya okunabilir değil.', E_ERROR);
//       }
//       throw  new Exception($filename. ' dosya bulunamadı.', E_ERROR);
//    }
//}
//
//
//if (!function_exists('is_writable_file')) {
//    /**
//     * Dosya varsa ve yazılabilirse true döndürür aksi halde exception fırlatır
//     *
//     * @param string $filename
//     * @return bool
//     * @throws Exception
//     */
//    function is_writable_file(string $filename)
//    {
//        if(is_file($filename)){
//            if(is_writable($filename)){
//
//                return true;
//            }
//            throw new Exception($filename. ' dosya yazılabilir değil.', E_ERROR);
//        }
//        throw  new Exception($filename. ' dosya bulunamadı.', E_ERROR);
//    }
//}
//
//
//if (!function_exists('is_readable_dir')) {
//    /**
//     * Dizin varsa ve okunabilirse true döndürür aksi halde exception fırlatır
//     *
//     * @param string $filename
//     * @return bool
//     * @throws Exception
//     */
//    function is_readable_dir(string $filename)
//    {
//        if(is_dir($filename)){
//            if(is_readable($filename)){
//
//                return true;
//            }
//            throw new Exception($filename. ' dizin okunabilir değil.', E_ERROR);
//        }
//        throw  new Exception($filename. ' dizin bulunamadı.', E_ERROR);
//    }
//}
//
//
//if (!function_exists('is_writable_dir')) {
//    /**
//     * Dizin varsa ve yazılabilirse true döndürür aksi halde exception fırlatır
//     *
//     * @param string $filename
//     * @return bool
//     * @throws Exception
//     */
//    function is_writable_dir(string $filename)
//    {
//        if(is_dir($filename)){
//            if(is_writable($filename)){
//
//                return true;
//            }
//            throw new Exception($filename. ' dizin yazılabilir değil.', E_ERROR);
//        }
//        throw  new Exception($filename. ' dizin bulunamadı.', E_ERROR);
//    }
//}
