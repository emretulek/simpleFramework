<?php


namespace Core\Cache;


Interface CacheInterface
{
    /**
     * Önbelleğe yeni bir değer ekler, anahtar varsa eklemez false döndürür
     *
     * @param $key
     * @param $value
     * @param bool $compress
     * @param int $expires
     * @return bool
     */
    public function add($key, $value, $compress = false, $expires = 2592000):bool;

    /**
     * Önbelleğe yeni bir değer ekler, anahtar varsa üzerine yazar
     *
     * @param $key
     * @param $value
     * @param bool $compress
     * @param int $expires
     * @return bool
     */
    public function set($key, $value, $compress = false, $expires = 2592000):bool;

    /**
     * Önbellekten ilgili anahtara ait değeri döndürür
     *
     * @param $key
     * @return mixed
     */
    public function get($key);

    /**
     * Önbellekten ilgili anahtara ait değeri siler
     *
     * @param $key
     * @return bool
     */
    public function delete($key):bool;

    /**
     * Tüm önbelleği temizler
     *
     * @return bool
     */
    public function flush():bool;
}
