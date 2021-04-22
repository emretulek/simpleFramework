<?php

namespace Core\Config;

class LoadConfigFiles
{

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
    public function loadFiles(): array
    {
        if (is_readable_dir($this->path) && is_writable_dir($this->path)) {

            $configFiles = array_diff(scandir($this->path), ['.', '..']);

            foreach ($configFiles as $configFile) {

                $fileName = pathinfo($configFile, PATHINFO_FILENAME);
                $fileContent =  require_once($this->path . '/' . $configFile);
                $this->configs[$fileName] = $fileContent;
            }

        } else {
            dump("[Config::path] {$this->path} okuma ve yazma izni olan bir dizin belirtin.");
        }

        return $this->configs;
    }
}
