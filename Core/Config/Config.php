<?php

namespace Core\Config;

/**
 * Class Config
 *
 * Config dosyalarınıdan gerekli ayarları yükler.
 */
class Config
{

    private static array $configs = [];
    const PATH = ROOT . 'config';


    /**
     * Dosyaların daha önce yüklenip yüklenmediğini kontrol eder.
     * Daha önce yüklenmemişse dosyaları yükler ve diziye alır.
     * Daha önce yüklenmişse dizi içeriğini döndürür.
     */
    private static function init()
    {
        self::$configs ? self::$configs : self::loadConfigFiles();
    }


    /**
     * Belirtilen dizindeki tüm config dosyalarını yükler.
     */
    private static function loadConfigFiles()
    {
        $configFiles = array_diff(scandir(self::PATH), ['.', '..']);

        foreach ($configFiles as $configFile) {

            $fileName = pathinfo($configFile, PATHINFO_FILENAME);
            self::$configs[$fileName] = require_once(self::PATH . '/' . $configFile);
        }
    }


    /**
     * İstenilen config değerlerini döndürür, bulamazsa false döner.
     *
     * @param string $configName dizi indexlerinin (.) ile birleştirilmiş isimleri (index1.index2.index3).
     * @return array|bool|mixed
     */
    public static function get(string $configName)
    {
        self::init();
        return dot_aray_get(self::$configs, $configName);
    }


    /**
     * Gönderilen index ve değeri varsa değiştirir, yoksa yenisini ekler.
     *
     * @param string $configName dizi indexlerinin (.) ile birleştirilmiş isimleri (index1.index2.index3).
     * @param mixed $value
     */
    public static function set(string $configName, $value)
    {
        self::init();
        dot_aray_set(self::$configs, $configName, $value);
    }

    /**
     * Girilen indexi bulup değerini null atar. Bulursa true, bulamazsa false döner.
     *
     * @param string $configName dizi indexlerinin (.) ile birleştirilmiş isimleri (index1.index2.index3).
     * @return bool
     */
    public static function remove($configName)
    {
        self::init();
        return dot_array_del(self::$configs, $configName);
    }
}
