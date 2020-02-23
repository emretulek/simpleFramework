<?php

namespace Core\Autoload;

use Core\Config\Config;
use Core\Router\Router;

/**
 * Class AutoLoad
 *
 * Sırasıyla sınıf, fonksiyon, alias, services ve routingleri başlatır.
 * Sınıflar çağırılmasıyla başlarken diğerleri config/autoload.php üzerinden belirlenir.
 */
class AutoLoad
{

    /**
     * AutoLoad constructor.
     */
    public function __construct()
    {
        spl_autoload_register(array($this, "loadClass"));
    }


    /**
     * @param string $class sınıf ismiyle birlikte namespace
     */
    private function loadClass(string $class)
    {
        $nameSpaces = explode('\\', $class);
        $classPath = '/' . implode('/', array_map('ucfirst', $nameSpaces));
        $filePath = ROOT . $classPath . EXT;

        if (file_exists($filePath)) {
            require_once($filePath);
        }
    }


    /**
     * config/autoload.php dosyasında belirtilen servis sınıflarını çağırır.
     */
    public function loadServices()
    {
        foreach (Config::get('autoload.services') as $service) {
            new $service;
        }
    }


    /**
     * config/autoload.php dosyasında belirtilen sınıfların takma isimlerini oluşturur.
     */
    public function loadAlias()
    {
        foreach (Config::get('autoload.alias') as $alias => $orginal) {

            class_alias($orginal, $alias);
        }
    }

    /**
     * config/autoload.php dosyasında belirtilen fonksiyon veya dosyaları çağırır.
     */
    public function loadFunctions()
    {
        foreach (Config::get('autoload.functions') as $key => $file) {
            if (file_exists($file)) {
                require_once($file);
            }
        }
    }


    /**
     * routes/ dizinindeki dosyaları a-z sıralamasıyla çağırır.
     * Tüm dosyalar çağırılınca Rototer::run methodunu çalıştırır.
     */
    public function loadRoutes()
    {
        foreach (Config::get('autoload.routes') as $key => $file) {
            if (file_exists($file)) {
                require_once($file);
            }
        }
        Router::start();
    }
}

