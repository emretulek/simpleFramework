<?php


namespace Core\Cache;


use Core\Config\Config;
use Core\Database\Database as DB;
use Core\Exceptions\Exceptions;
use Exception;

class DatabaseCache implements CacheInterface
{
    protected $table;

    public function __construct()
    {
        try {

            $this->table = Config::get("app.cache.database.table");

            if (DB::query("select 1 from {$this->table} limit 1")) {
                $this->clear();
            }else{
                throw new Exception(Config::get("app.cache.database.table"). " belirtilen bellek tablosu bulunamadı.", E_ERROR);
            }

        }catch (Exception $e){
            Exceptions::debug($e);
        }
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
        $value = serialize($value);
        $value = $compress ? bzcompress($value) : $value;

        if($id = DB::getVar("select id from {$this->table} where `key` = ?", [$key])) {
            return DB::update("update {$this->table} set  `key` = ?, value = ?, compress = ?, expires = FROM_UNIXTIME(?) where id = ?", [$key, $value, $compress, time() + $expires, $id]);
        }else{
            return DB::insert("insert into {$this->table} set `key` = ?, value = ?, compress = ?, expires = FROM_UNIXTIME(?)", [$key, $value, $compress, time() + $expires]);
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
     */
    public function add($key, $value, int $compress = 0, $expires = 2592000): bool
    {
        $value = serialize($value);
        $value = $compress ? bzcompress($value) : $value;

        if(!DB::getVar("select id from {$this->table} where `key` = ?", [$key])) {
            return DB::insert("insert into {$this->table} set `key` = ?, value = ?, compress = ?, expires = FROM_UNIXTIME(?)", [$key, $value, $compress, time() + $expires]);
        }
        return false;
    }


    /**
     * Önbellekten ilgili anahtara ait değeri döndürür
     *
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        if($value = DB::getRow("select * from $this->table where `key` = ?", [$key])) {

            if($value->compress){
                $value->value = bzdecompress($value->value);
            }

            return unserialize($value->value);
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
        return DB::delete("delete from {$this->table} where `key` = ?", [$key]);
    }

    /**
     * Tüm önbelleği temizler
     *
     * @return bool
     */
    public function flush(): bool
    {
        return DB::delete("delete from {$this->table}");
    }

    /**
     * Süresini aşan cache bilgilerini temizler
     */
    private function clear()
    {
        return DB::delete("delete from {$this->table} where expires < FROM_UNIXTIME(".time().")");
    }
}
