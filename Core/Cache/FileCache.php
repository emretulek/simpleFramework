<?php

namespace Core\Cache;

use Closure;
use Exception;

class FileCache extends BaseCache
{

    protected string $path;

    public function __construct(array $config)
    {
        $this->path = $config['path'];

        if (!is_readable_dir($this->path) && !is_writable_dir($this->path)) {
            throw new Exception("Cache dizini ({$this->path}) okuma ve yazma izinleri verilmedi.");
        }

        $this->fileCacheGc();
    }


    /**
     *  Önbellekten ilgili anahtara ait değeri döndürür
     * @param string $key
     * @param null $default
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function get(string $key, $default = null)
    {
        if ($fileInfo = $this->getFileInfo($key)) {
            return $fileInfo['content'];
        }

        if ($default instanceof Closure) {
            return $default();
        }

        return $default;
    }

    /**
     * Önbelleğe yeni bir değer ekler, anahtar varsa üzerine yazar
     *
     * @param string $key
     * @param $value
     * @param int|null|\DateInterval $ttl
     * @return bool
     */
    public function set(string $key, $value, $ttl = null): bool
    {
        $file = $this->setFileName($key);
        $content = $this->expires($ttl) . serialize($value);

        return (bool)file_put_contents($file, $content, LOCK_EX);
    }

    /**
     * Önbelleğe yeni bir değer ekler, anahtar varsa false döner
     *
     * @param string $key
     * @param $value
     * @param int|null|\DateInterval $ttl
     * @return bool
     * @throws InvalidArgumentException
     */
    public function add(string $key, $value, $ttl = null): bool
    {
        $file = $this->setFileName($key);

        if (is_readable_file($file)) {
            return false;
        }

        return $this->set($key, $value, $ttl);
    }

    /**
     * Önbellekte veri varsa getirir yoksa oluşturuğ default değeri döndürür
     * @param string $key
     * @param int|null|\DateInterval $ttl
     * @param mixed|Closure $default
     * @return mixed
     */
    public function getSet(string $key, $ttl = null, $default = null)
    {
        if ($fileInfo = $this->getFileInfo($key)) {
            return $fileInfo['content'];
        }

        if ($default instanceof Closure) {
            $value = $default();
        } else {
            $value = $default;
        }

        $this->set($key, $value, $ttl);

        return $value;
    }

    /**
     * Önbellekten ilgili anahtara ait değeri siler
     *
     * @param string $key
     * @return bool
     * @throws InvalidArgumentException
     */
    public function delete(string $key): bool
    {
        $file = $this->setFileName($key);

        if (is_writable_file($file)) {
            return unlink($file);
        }

        return true;
    }

    /**
     * Tüm önbelleği temizler
     * @return bool
     */
    public function clear(): bool
    {
        $files = glob($this->path . '/*.cache');
        try {
            foreach ($files as $file) {
                unlink($file);
            }
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Çoklu önbellek listesi
     * @param array $keys anahtar değer ilişkili liste
     * @return array A list of key
     * @throws InvalidArgumentException
     */
    public function getMultiple(array $keys): array
    {
        $items = [];

        foreach ($keys as $key) {
            $items[$key] = $this->get($key);
        }

        return $items;
    }

    /**
     * Çoklu önbellekleme
     * @param array $items anahtar değer ilişkili liste
     * @param int|null|\DateInterval $ttl geçerlilik süresi
     * @return bool
     * @throws InvalidArgumentException
     */
    public function setMultiple(array $items, $ttl = null): bool
    {
        $result = [];
        foreach ($items as $key => $value) {
            $result[$key] = $this->set($key, $value, $ttl);
        }

        return !in_array(false, $result);
    }

    /**
     * Çoklu önbellekten veri silme
     * @param array $keys
     * @return bool
     * @throws InvalidArgumentException
     */
    public function deleteMultiple(array $keys): bool
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->delete($key);
        }

        return !in_array(false, $result);
    }

    /**
     * Önbellekte anahtarın olup olmadığını kontrol eder
     * @param string $key
     * @return bool
     * @throws InvalidArgumentException
     */
    public function has(string $key): bool
    {
        if ($this->getFileInfo($key)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $key
     * @param int $value
     * @return int|false
     */
    public function increment(string $key, $value = 1)
    {
        $valueOld = $this->get($key);

        if ($valueOld !== null || $valueOld === false) {
            $valueNew = $valueOld + $value;
            $this->set($key, $valueNew);
            return $valueNew;
        }

        $this->set($key, $value);
        return $value;
    }

    /**
     * @param string $key
     * @param int $value
     * @return int|false
     */
    public function decrement(string $key, $value = 1)
    {
        $valueOld = $this->get($key);

        if ($valueOld !== null || $valueOld === false) {
            $valueNew = $valueOld - $value;
            $this->set($key, $valueNew);
            return $valueNew;
        }

        $this->set($key, $value);
        return $value;
    }

    /**
     * @param $key
     * @return string
     */
    private function setFileName($key): string
    {
        return $this->path . '/' . md5($key) . '.cache';
    }


    /**
     * @param string $key
     * @return array|false
     */
    private function getFileInfo(string $key)
    {
        $file = $this->setFileName($key);

        if (is_readable_file($file)) {
            $content = file_get_contents($file);
            $expire = substr($content,0, 10);
            $value = unserialize(substr($content, 10));

            if ($expire > time()) {
                return ['expires' => $expire, 'content' => $value];
            }

            $this->delete($key);
        }

        return false;
    }

    /**
     * @param float $gc
     * @param float|int $maxLifeTime
     */
    private function fileCacheGc($gc = 0.001, $maxLifeTime = 60 * 60 * 24 * 30): void
    {
        $files = glob($this->path . '/*.cache');

        if (count($files) == 0) {
            return;
        }

        $gcCount = ceil(count($files) * $gc);
        shuffle($files);

        foreach ($files as $file) {

            if (is_readable_file($file) && filemtime($file) < time() - $maxLifeTime) {
                $content = file_get_contents($file);
                $expire = substr($content,0, 10);

                if ($expire < time()) {
                    unlink($file);
                }
            }

            if (!--$gcCount) {
                return;
            }
        }
    }
}
