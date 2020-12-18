<?php

namespace Core\Config;

/**
 * Class Config
 */
class Config
{
    private array $configs = [];

    /**
     * Config constructor.
     * @param array $configs
     */
    public function __construct(array $configs)
    {
        $this->configs = $configs;
    }

    /**
     * İstenilen config değerlerini döndürür, bulamazsa false döner.
     *
     * @param string $key dizi indexlerinin (.) ile birleştirilmiş isimleri (index1.index2.index3).
     * @return mixed
     */
    public function get(string $key)
    {
        return dot_aray_get($this->configs, $key);
    }


    /**
     * Gönderilen index ve değeri varsa değiştirir, yoksa yenisini ekler.
     *
     * @param string $key dizi indexlerinin (.) ile birleştirilmiş isimleri (index1.index2.index3).
     * @param mixed $value
     */
    public function set(string $key, $value)
    {
        dot_aray_set($this->configs, $key, $value);
    }


    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key):bool
    {
        return dot_aray_get($this->configs, $key) !== null;
    }


    /**
     * @return array
     */
    public function all():array
    {
        return $this->configs;
    }

    /**
     * Girilen indexi bulup değerini null atar. Bulursa true, bulamazsa false döner.
     *
     * @param string $key dizi indexlerinin (.) ile birleştirilmiş isimleri (index1.index2.index3).
     * @return bool
     */
    public function remove(string $key):bool
    {
        return dot_array_del($this->configs, $key);
    }
}
