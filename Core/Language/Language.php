<?php

namespace Core\Language;

use Core\Config\Config;
use Core\Exceptions\Exceptions;
use Core\Http\Request;
use Exception;

/**
 * Class Language
 * Uygulamaya session üzerinden çoklu dil desteği kazandırır.
 */
class Language
{
    private static array $default = [];
    private static array $languages;
    private static array $translate;


    public static function init()
    {
        $default = [
            'key' => Config::get('app.language.key'),
            'name' => Config::get('app.language.name'),
            'local' => Config::get('app.language.local')
        ];

        self::add($default['key'], $default['name'], $default['local']);
        self::setDefault($default['key']);
        self::setActive($default['key']);
    }

    /**
     * Url üzerinden aktif dili belirler, aktif dil default dil ile aynı ise url yapısında gösterilmez.
     * @TODO site.com/en-us/contact
     */
    public static function useUrl()
    {
        $segments = Request::segments();

        if(isset($segments[0])) {

            Language::setActive($segments[0]);

            //default dil ise adres satırında gösterme
            if (array_shift($segments) == Language::getDefault()->key) {
                redirect(Request::baseUrl().implode('/', $segments));
            }
        }
    }

    /**
     * Ön tanımlı dili ayarlar
     * @param $key
     * @return bool
     */
    public static function setDefault($key)
    {
        if (self::exists($key)) {
            self::$default = [
                'key' => $key,
                'name' => self::$languages[$key]['name'],
                'local' => self::$languages[$key]['local'],
            ];

            return true;
        }

        return false;
    }

    /**
     * Öntanımlı dil bilgilerini döndürür
     */
    public static function getDefault()
    {
        return self::$default ? (object) self::$default : self::$default;
    }

    /**
     * Dili değiştirir.
     *
     * @param string $key
     * @return bool
     */
    public static function setActive(string $key)
    {
        if (self::exists($key)) {
            $_SESSION['lang'] = (object) array_merge(['key' => $key], self::$languages[$key]);
            self::loadFiles($key);

            return true;
        }

        return false;
    }


    /**
     * @return object [key,name,local]
     *
     * Aktif dil özelliklerini döndürür
     */
    public static function getActive()
    {
        return $_SESSION['lang'] ?? self::getDefault();
    }


    /**
     * Kullanılacak dil dosyasını yükler.
     *
     * @param string $key dosyanın kullanılacağı dil anahtarı.
     * @param string $file_path yüklenecek dosya yolu
     * @return bool|array
     */
    public static function loadFile(string $key, string $file_path)
    {
        if (is_readable_file($file_path)) {
            return self::$translate[$key][pathinfo($file_path,  PATHINFO_FILENAME)] = require($file_path);
        }else {
            try {
                throw  new Exception('Dil dosyası bulunamadı. ' . $file_path, E_NOTICE);
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
    private static function loadFiles($key)
    {
        $fullPath = ROOT . Config::get('path.lang') . '/' . $key;

        if(is_readable_dir($fullPath)) {
            $files = array_diff(scandir($fullPath), ['..', '.']);

            foreach ($files as $file) {
                self::loadFile($key, $fullPath.'/'.$file);
            }
        }
    }



    /**
     * Indexi girilen çeviriyi döndürür
     *
     * @param string $key dosya ismi ile birlikte dizi indexi örn; {lang/tr/home.php, $title} için {home.title}
     * @param mixed ...$args
     * @return mixed
     */
    public static function translate(string $key, ...$args)
    {
        if($translated = dot_aray_get(self::$translate, self::getDefault()->key.".".$key)){
            return is_array($translated) ? $translated : vsprintf($translated, $args);
        }

        if(self::getDefault() && $default = dot_aray_get(self::$translate, self::getDefault()->key.".".$key)){
            return is_array($translated) ? $translated : vsprintf($translated, $args);
        }

        return vsprintf($key, $args);
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
        return dot_aray_set(self::$translate[self::getActive()->key], $key, $value);
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

    /**
     * Kullanılabilir dillere yenir bir dil ekler
     * @param string $key
     * @param string $name
     * @param string $local
     */
    public static function add(string $key, string $name, string $local)
    {
        self::$languages[$key]['name'] = $name;
        self::$languages[$key]['local'] = $local;
    }

    /**
     * Kullanılabilir dillerden keyi girileni kaldırır
     * @param string $lang_key
     */
    public static function remove(string $lang_key)
    {
        unset(self::$languages[$lang_key]);
    }


    /**
     * Dilin adres satırında gösterilecek formu.
     * Seçili dil default dil ile aynı ise boş dönecektir.
     * @return string
     */
    public static function prefix()
    {
        if(self::getDefault()) {
            return self::getActive()->key == self::getDefault()->key ? '' : self::getActive()->key;
        }

        return '';
    }
}
