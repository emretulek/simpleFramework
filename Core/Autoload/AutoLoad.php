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
    private array $config = [];
    /**
     * AutoLoad constructor.
     */
    public function __construct()
    {
        spl_autoload_register(array($this, "loadClass"));
        $this->config = Config::get('autoload');
    }


    /**
     * @param string $class sınıf ismiyle birlikte namespace
     */
    private function loadClass(string $class)
    {
        $nameSpaces = explode('\\', $class);
        $classPath = implode('/', array_map('ucfirst', $nameSpaces));
        $filePath = ROOT . $classPath . EXT;

        if (is_readable_file($filePath)) {
            require_once($filePath);
        }
    }


    /**
     * config/autoload.php dosyasında belirtilen servis sınıflarını çağırır.
     */
    public function loadServices()
    {
        foreach ($this->config['services'] as $service) {
            new $service;
        }
    }


    /**
     * config/autoload.php dosyasında belirtilen sınıfların takma isimlerini oluşturur.
     */
    public function loadAlias()
    {
        foreach ($this->config['alias'] as $alias => $orginal) {

            class_alias($orginal, $alias);
        }
    }

    /**
     * config/autoload.php dosyasında belirtilen fonksiyon veya dosyaları çağırır.
     */
    public function loadFunctions()
    {
        foreach ($this->config['functions'] as $key => $file) {
            if (is_readable_file($file)) {
                require_once($file);
            }
        }
    }


    /**
     * routes/ dizinindeki dosyaları a-z sıralamasıyla çağırır.
     * Tüm dosyalar çağırılınca Router::start methodunu çalıştırır.
     */
    public function loadRoutes()
    {
        foreach ($this->config['routes'] as $key => $file) {
            if (is_readable_file($file)) {
                require_once($file);
            }
        }
        Router::start();
    }
}

