<?php

namespace Core\Cache;

use Closure;
use Core\Database\Database;
use Core\Database\QueryBuilder;
use Exception;

class DatabaseCache extends BaseCache
{
    protected Database $database;
    protected string $table;

    /**
     * DatabaseCache constructor.
     * @param Database $database
     * @param array $config
     * @throws Exception
     */
    public function __construct(Database $database, array $config)
    {
        $this->database = $database;
        $this->table = $config['table'];
        $tableExists = $database->table("INFORMATION_SCHEMA.TABLES")
            ->select("COUNT(1)")
            ->where("TABLE_NAME", $this->table)
            ->getVar();

        if ($tableExists == 0) {
            throw new Exception("{$this->table} belirtilen ön bellek tablosu bulunamadı.", E_ERROR);
        }
    }


    /**
     *  Önbellekten ilgili anahtara ait değeri döndürür
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        if ($item = $this->table()->select()->where("key", $key)->getRow()) {

            if ($item->expires > time()) {
                return unserialize($item->value);
            }

            $this->delete($key);
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
     * @param mixed $value
     * @param int|null|\DateInterval $ttl
     * @return bool
     * @throws Exception
     */
    public function set(string $key, $value, $ttl = null): bool
    {
        $value = serialize($value);
        $expires = $this->expires($ttl);

        try {
            $result = $this->table()->insert(compact('key', 'value', 'expires'));
        } catch (Exception $e) {
            $result = $this->table()->where('key', $key)->update(compact('key', 'value', 'expires'));
        }

        return $result > 0;
    }


    /**
     * Önbelleğe yeni bir değer ekler, anahtar varsa false döner
     *
     * @param string $key
     * @param mixed $value
     * @param int|null|\DateInterval $ttl
     * @return bool
     */
    public function add(string $key, $value, $ttl = null): bool
    {
        $value = serialize($value);
        $expires = $this->expires($ttl);

        try {
            $result = $this->table()->insert(compact('key', 'value', 'expires'));
        } catch (Exception $e) {
            return false;
        }

        return $result > 0;
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
        if ($value = $this->get($key)) {
            return $value;
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
     */
    public function delete(string $key): bool
    {
        return $this->table()->delete(['key' => $key]);
    }

    /**
     * Tüm önbelleği temizler
     * @return bool
     */
    public function clear(): bool
    {
        return $this->table()->delete(null, false,true);
    }

    /**
     * Çoklu önbellek listesi
     * @param array $keys liste
     * @return array A list of key
     */
    public function getMultiple(array $keys): array
    {
        $items = [];

        foreach ($keys as $key) {
            if ($item = $this->get($key)) {
                $items[$key] = $item;
            } else {
                $items[$key] = null;
            }
        }

        return $items;
    }

    /**
     * Çoklu önbellekleme
     * @param array $items anahtar değer ilişkili liste
     * @param int|null|\DateInterval $ttl geçerlilik süresi
     * @return bool
     */
    public function setMultiple(array $items, $ttl = null): bool
    {
        try {
            $this->database->transaction();
            foreach ($items as $key => $item) {
                $this->set($key, $item, $ttl);
            }
            $this->database->commit();
            return true;
        } catch (Exception $e) {
            $this->database->rollBack();
            return false;
        }
    }

    /**
     * Çoklu önbellekten veri silme
     * @param array $keys
     * @return bool
     */
    public function deleteMultiple(array $keys): bool
    {
        try {
            $this->database->transaction();
            foreach ($keys as $key) {
                $this->delete($key);
            }
            $this->database->commit();
            return true;
        } catch (Exception $e) {
            $this->database->rollBack();
            return false;
        }
    }

    /**
     * Önbellekte anahtarın olup olmadığını kontrol eder
     * @param string $key
     * @return bool
     * @throws Exception
     */
    public function has(string $key): bool
    {
        return (bool)$this->table()->select('COUNT(1)')->where('key', $key)->getVar();
    }


    /**
     * @param string $key
     * @param int $value
     * @return int
     */
    public function increment(string $key, $value = 1):int
    {
        $ttl = $this->expires(null);

        if ($item = $this->get($key)) {
            $value = (int)$item + $value;
        }

        $this->set($key, $value, $ttl);

        return $value;
    }

    /**
     * @param string $key
     * @param int $value
     * @return int
     */
    public function decrement(string $key, $value = 1):int
    {
        $ttl = $this->expires(null);

        if ($item = $this->get($key)) {
            $value = (int)$item - $value;
        }

        $this->set($key, $value, $ttl);

        return $value;
    }

    /**
     * @return QueryBuilder
     */
    private function table(): QueryBuilder
    {
        return $this->database->table($this->table);
    }
}
