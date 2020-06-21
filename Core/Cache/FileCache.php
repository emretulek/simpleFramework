<?php


namespace Core\Cache;


use Core\Config\Config;
use Exception;

class FileCache implements CacheInterface
{

    protected string $path;

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
     * @throws Exception
     */
    public function set($key, $value, int $compress = 0, $expires = 2592000): bool
    {
        $fileName = $this->setFileName($key);

        $value = serialize($value);
        $value = $compress ? bzcompress($value) : $value;
        $value .= PHP_EOL . (int)$compress;
        $value .= PHP_EOL . $expires;

        if (is_writable_dir($this->path)) {
            return file_put_contents($fileName, $value) !== false;
        }else{
            throw new Exception("Dizin yazılabilir değil.", E_NOTICE);
        }
    }


    /**
     * Önbelleğe yeni bir değer ekler, anahtar varsa eklemez false döndürür
     *
     * @param $key
     * @param $value
     * @param int $compress
     * @param int $expires
     * @return bool
     * @throws Exception
     */
    public function add($key, $value, int $compress = 0, $expires = 2592000): bool
    {
        $fileName = $this->setFileName($key);

        if (is_writable_file($fileName)) {
            return false;
        }

        return $this->set($key, $value, $compress, $expires);
    }


    /**
     * Önbellekten ilgili anahtara ait değeri döndürür
     * @param $key
     * @return bool|mixed
     */
    public function get($key)
    {
        $fileName = $this->setFileName($key);

        if (is_readable_file($fileName)) {

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

        if (is_writable_file($fileName)) {
            return unlink($fileName);
        }

        return false;
    }

    /**
     * Tüm önbelleği temizler
     * @return bool
     * @throws Exception
     */
    public function flush(): bool
    {
        $files = glob($this->path . '/*.cache');

        if($files) {
            foreach ($files as $file) {
                if (is_writable_file($file)) {
                    return unlink($file);
                }else{
                    throw new Exception($file." Dosya silinemiyor.", E_NOTICE);
                }
            }
        }

        return false;
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
     * @param float $gc
     * @param int $lifeTime
     * @throws Exception
     */
    private function fileCacheGc($gc = 0.001, $lifeTime = 180)
    {
        $files = glob($this->path . '/*.cache');

        $gcCount = ceil(count($files) * $gc);
        shuffle($files);

        if($files) {
            foreach ($files as $file) {

                if (filemtime($file) < time() - $lifeTime) {
                    if (is_writable_file($file)) {
                        unlink($file);
                    }else{
                        throw new Exception("Dosya silinemiyor.", E_NOTICE);
                    }
                }

                if (!--$gcCount) {
                    break;
                }
            }
        }

        $files = null;
    }
}
