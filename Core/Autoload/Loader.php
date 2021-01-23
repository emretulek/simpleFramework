<?php

namespace Core\Autoload;


class Loader
{
    /**
     * Loader constructor.
     * @param array $files
     */
    public function __construct(array $files = [])
    {
        $this->loadFile($files);
        spl_autoload_register(array($this, "loadClass"));
    }


    /**
     * @param string $class sınıf ismiyle birlikte namespace
     */
    private function loadClass(string $class)
    {
        $nameSpaces = explode('\\', $class);
        $classPath = implode(DS, $nameSpaces);
        $filePath = ROOT . DS . $classPath . EXT;

        if (is_readable_file($filePath)) {
            require_once($filePath);
        }
    }

    /**
     * @param array $files uygulama öncesi yüklenecek dosyalar
     */
    private function loadFile(array $files)
    {
        foreach ($files as $file) {
            if (is_file($file)) {
                require_once($file);
            }
        }
    }
}

