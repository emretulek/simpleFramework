<?php 
/**
 * @Created 03.12.2020 16:51:32
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class LoadConfigFiles
 * @package Core\Config
 */


namespace Core\Config;


class LoadConfigFiles {

    protected string $path = '';
    protected array $configs = [];

    /**
     * LoadConfigFiles constructor.
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @return array
     */
    public function loadFiles():array
    {
        if(is_readable_dir($this->path) && is_writable_dir($this->path)){

            $configFiles = array_diff(scandir($this->path), ['.', '..']);

            foreach ($configFiles as $configFile) {

                $fileName = pathinfo($configFile, PATHINFO_FILENAME);
                $this->configs[$fileName] = require_once($this->path . '/' . $configFile);
            }

        }else{
            dump("[Config::path] {$this->path} okuma ve yazma izni olan bir dizin belirtin.");
        }

        return $this->configs;
    }
}
