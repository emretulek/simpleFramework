<?php

namespace Core\Language;

use Core\Config\Config;
use Core\Exceptions\Exceptions;
use Exception;

/**
 * Class Language
 * Uygulamaya url kullanarak çoklu dil desteği kazandırır.
 */
class Language
{
    private static $default = array('key' => null, 'name' => null, 'local' => null);
    private static $languages = array();
    private static $translate = array();


    /**
     * Öntanımlı dili ayarlar
     * @param $key
     */
    public static function setDefault($key)
    {
        if (self::exists($key)) {
            self::$default = [
                'key' => $key,
                'name' => self::$languages[$key]['name'],
                'local' => self::$languages[$key]['local'],
            ];
            self::set($key);
        }
    }

    /**
     * Öntanımlı dil bilgilerini döndürür
     */
    public static function getDefault()
    {
        return self::$default;
    }

    /**
     * Dili değiştirir.
     *
     * @param string $key
     * @return bool
     */
    public static function set(string $key)
    {
        if (self::exists($key)) {

            $_SESSION['lang'] = array_merge(['key' => $key], self::$languages[$key]);
            self::loadFiles($key);

            return true;
        }

        return false;
    }


    /**
     * @return bool|string
     *
     * Aktif dil anahtarını döndürür.
     */
    public static function get()
    {
        return $_SESSION['lang'] ?? false;
    }


    /**
     * Kullanılacak dil dosyasını yükler.
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
        }else {

            try {
                throw  new Exception('Dil dosyası bulunamadı. ' . $fullPath, E_NOTICE);
            } catch (Exception $e) {
                Exceptions::debug($e);
            }
        }
        return false;
    }


    /**
     * lang dizini altındaki ilgili tüm dil dosyalarını yükler
     *
     * @param $key
     */
    public static function loadFiles($key)
    {
        $fullPath = ROOT . Config::get('path.lang') . '/' . $key;
        $files = array_map(function($file) {

            return  pathinfo($file, PATHINFO_FILENAME);
        },
            array_diff(scandir($fullPath), array('..', '.'))
        );

        foreach ($files as $file){
            self::loadFile($key, $file);
        }
    }



    /**
     * Indexi girilen çeviriyi döndürür
     *
     * @param string $key dosya ismi ile birlikte dizi indexi örn; {lang/tr/home.php, $title} için {home.title}
     * @return mixed
     */
    public static function translate(string $key)
    {
        if($translated = dot_aray_get(self::$translate[self::get()], $key)){
            return $translated;
        }

        return dot_aray_get(self::$translate[self::$default['key']], $key);

    }


    /**
     * Dile yeni çeviriler eklemek için kullanılır
     *
     * @param string $key nokta ile birleştirilmiş dizi indexleri
     * @param mixed $value çeviri
     * @return mixed
     */
    public static function newTranslate(string $key, $value)
    {
        return dot_aray_set(self::$translate[self::get()], $key, $value);
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
        if (array_key_exists($lang_key, self::$languages)) {
            return true;
        }
        return false;
    }
}

