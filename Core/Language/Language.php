<?php

namespace Core\Language;

use Core\Config\Config;
use Core\Log\LogException;

/**
 * Class Language
 * Uygulamaya url kullanarak çoklu dil desteği kazandırır.
 */
class Language
{
    private static $languages = array();
    private static $translate = array();


    /**
     * Ön tanımlı dil anahtarını döndürür örn; {tr, en, vb.}
     *
     * @return array|bool|mixed
     */
    public static function default()
    {
        return Config::get('app.language');
    }


    /**
     * Ön tanımlı dil ve yerel zamanı değiştirir.
     *
     * @param string $key yeni dil anahtarı örn; {tr, en, vb.}
     */
    public static function set(string $key)
    {
        if (self::exists($key)) {
            $_SESSION['lang'] = $key;
        }else{
            $_SESSION['lang'] = self::default();
        }

        setlocale(LC_ALL, self::$languages[$_SESSION['lang']]['local']);
    }


    /**
     * @return string
     *
     * Aktif dil anahtarını döndürür.
     */
    public static function get():string
    {
        return $_SESSION['lang'] ?? '';
    }


    /**
     * Kullanılacak dil dosyalarını yükler.
     *
     * @param string $key dosyanın kullanılacağı dil anahtarı.
     * @param string $file yüklenecek dosya adı, uzantı olmadan örn; {settings, admin/settings}
     * @return bool
     */
    public static function loadFile(string $key, string $file)
    {
        $fullPath = ROOT . Config::get('path.lang') . '/' . $key . '/' . $file . EXT;

        if (file_exists($fullPath)) {
            self::$translate[$key][basename($file)] = require($fullPath);
            return self::$translate[$key][basename($file)];
        }

        $fullPath = ROOT . Config::get('path.lang') . '/' . self::default() . '/' . $file . EXT;

        if (file_exists($fullPath)) {
            self::$translate[$key][basename($file)] = require($fullPath);
            return self::$translate[$key][basename($file)];
        }

        try {
            throw  new LogException('Dil dosyası bulunamadı. ' . $fullPath, E_WARNING);
        } catch (LogException $exception) {
            $exception->debug();
        }
        return false;
    }


    /**
     * Indexi girilen çeviriyi döndürür
     *
     * @param string $key dosya ismi ile birlikte dizi indexi örn; {lang/tr/home.php, $title} için {home.title}
     * @return mixed
     */
    public static function translate(string $key)
    {
        return dot_aray_get(self::$translate[self::get()], $key);
    }


    /**
     * Kullanılabilir dilleri ve ayarlarını ekler.
     *
     * @param $key
     * @param $name
     * @param $local
     */
    public static function add($key, $name, $local)
    {
        self::$languages[$key]['name'] = $name;
        self::$languages[$key]['local'] = $local;
    }


    /**
     * Anahtarı girilen dilin kullanılabilir olup olmadığına bakar
     *
     * @param string $lang_key dil anahtarı.
     * @return bool
     */
    public static function exists(string $lang_key)
    {
        if ($lang_key && array_key_exists($lang_key, self::$languages)) {
            return true;
        }
        return false;
    }
}

