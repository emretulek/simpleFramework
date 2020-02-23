<?php


namespace Core\Cache;


use Core\Config\Config;
use Exception;
use Exceptions;

class FileCache implements CacheInterface
{

    protected $path;

    public function __construct()
    {
        $this->path = ROOT . Config::get('path.cache');
        $this->fileCacheGc();
    }


    /**
     * Önbelleğe yeni bir değer ekler, anahtar varsa üzerine yazar
     *
     * @param $key
     * @param $value
     * @param int $compress
     * @param int $expires
     * @return bool
     */
    public function set($key, $value, int $compress = 0, $expires = 2592000): bool
    {
        $fileName = $this->setFileName($key);

        $value = serialize($value);
        $value = $compress ? bzcompress($value) : $value;
        $value .= PHP_EOL . (int)$compress;
        $value .= PHP_EOL . $expires;

        try {

            if ($this->hasWritable($this->path)) {
                return file_put_contents($fileName, $value) !== false;
            }

        } catch (Exception $e) {
            Exceptions::debug($e);
        }

        return false;
    }


    /**
     * Önbelleğe yeni bir değer ekler, anahtar varsa eklemez false döndürür
     *
     * @param $key
     * @param $value
     * @param int $compress
     * @param int $expires
     * @return bool
     */
    public function add($key, $value, int $compress = 0, $expires = 2592000): bool
    {
        $fileName = $this->setFileName($key);

        if (file_exists($fileName)) {
            return false;
        }

        return $this->set($key, $value, $compress, $expires);
    }


    /**
     * Önbellekten ilgili anahtara ait değeri döndürür
     *
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        $fileName = $this->setFileName($key);

        if (file_exists($fileName)) {

            try {

                if ($this->hasReadable($fileName)) {

                    $value = file_get_contents($fileName);
                    $lines = explode(PHP_EOL, $value);
                    $expires = array_pop($lines);
                    $compress = array_pop($lines);
                    $value = implode(PHP_EOL, $lines);

                    if (filemtime($fileName) < time() - $expires) {
                        unlink($fileName);
                        return false;
                    }

                    if ($compress) {
                        $value = bzdecompress($value);
                    }

                    return unserialize($value);
                }

            } catch (Exception $e) {
                Exceptions::debug($e);
            }
        }

        return false;
    }

    /**
     * Önbellekten ilgili anahtara ait değeri siler
     *
     * @param $key
     * @return bool
     */
    public function delete($key): bool
    {
        $fileName = $this->setFileName($key);

        if (file_exists($fileName)) {

            try {

                if ($this->hasWritable($fileName)) {
                    return unlink($fileName);
                }

            } catch (Exception $e) {
                Exceptions::debug($e);
            }
        }

        return true;
    }

    /**
     * Tüm önbelleği temizler
     *
     * @return bool
     */
    public function flush(): bool
    {
        $files = glob($this->path . '/*.cache');

        try {
            foreach ($files as $file) {
                if ($this->hasWritable($file)) {
                    return unlink($file);
                }
            }
        } catch (Exception $e) {
            Exceptions::debug($e);
        }
        return true;
    }


    /**
     * @param $key
     * @return string
     */
    private function setFileName($key)
    {
        return $this->path . '/' . substr(md5($key), 16) . '.cache';
    }

    /**
     *
     * @param float $gc
     * @param int $lifeTime
     */
    private function fileCacheGc($gc = 0.001, $lifeTime = 180)
    {
        $files = glob($this->path . '/*.cache');

        $gcCount = ceil(count($files) * $gc);
        shuffle($files);

        try {
            foreach ($files as $file) {

                if (filemtime($file) < time() - $lifeTime) {
                    if ($this->hasWritable($file)) {
                        unlink($file);
                    }
                }

                if (!--$gcCount) {
                    break;
                }
            }
        }catch (Exception $e){
            Exceptions::debug($e);
        }

        $files = null;
    }


    /**
     * @param $filename
     * @return bool
     * @throws Exception
     */
    private function hasWritable($filename)
    {
        if(is_writable($filename)){
            return true;
        }

        throw new Exception($filename.' dosya yazılabilir değil', E_WARNING);
    }

    /**
     * @param $filename
     * @return bool
     * @throws Exception
     */
    private function hasReadable($filename)
    {
        if(is_readable($filename)){
            return true;
        }

        throw new Exception($filename.' dosya okunabilir değil.', E_WARNING);
    }
}
