<?php


namespace Core\Cache;


use Closure;
use RuntimeException;

class ApcuCache extends BaseCache
{

    /**
     * ApcuCache constructor.
     */
    public function __construct()
    {
        if (!extension_loaded("apcu")) {
            throw new RuntimeException('apcu eklentisi kurulu değil.');
        }

        if (!apcu_enabled()) {
            throw new RuntimeException('apcu kullanılabilir değil.');
        }
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
        if ($value = apcu_fetch($key, $success)) {
            return $value;
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
     * @throws InvalidArgumentException
     */
    public function set(string $key, $value, $ttl = null): bool
    {
        return apcu_store($key, $value, $this->ttl($ttl));
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
        return apcu_add($key, $value, $this->ttl($ttl));
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
        if (!$default instanceof Closure) {
            $default = function () use ($default) {
                return $default;
            };
        }

        return apcu_entry($key, $default, $this->ttl($ttl));
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
        return apcu_delete($key);
    }

    /**
     * Tüm önbelleği temizler
     * @return bool
     */
    public function clear(): bool
    {
        return apcu_clear_cache();
    }

    /**
     * Çoklu önbellek listesi
     * @param array $keys anahtar değer ilişkili liste
     * @return array A list of key
     * @throws InvalidArgumentException
     */
    public function getMultiple(array $keys): array
    {
        return apcu_fetch($keys);
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
        return apcu_store($items, null, $this->ttl($ttl));
    }

    /**
     * Çoklu önbellekten veri silme
     * @param array $keys
     * @return bool
     * @throws InvalidArgumentException
     */
    public function deleteMultiple(array $keys): bool
    {
        return apcu_delete($keys);
    }

    /**
     * Önbellekte anahtarın olup olmadığını kontrol eder
     * @param string $key
     * @return bool
     * @throws InvalidArgumentException
     */
    public function has(string $key): bool
    {
        return apcu_exists($key);
    }


    /**
     * @param string $key
     * @param int $value
     * @return int|false
     */
    public function increment(string $key, $value = 1)
    {
        return apcu_inc($key, $value);
    }

    /**
     * @param string $key
     * @param int $value
     * @return int|false
     */
    public function decrement(string $key, $value = 1)
    {
        return apcu_dec($key, $value);
    }
}
