<?php

namespace Core\Hook;

class Hook
{
    protected static array $storages = [];

    /**
     * Yeni bir kanca ekler
     * @param string $name
     * @param callable $callable
     * @param int $priority
     * @return bool
     */
    public function add(string $name, callable $callable, int $priority = 50): bool
    {
        if (isset(self::$storages[$name][$priority])) {
            self::$storages[$name][] = $callable;
        } else {
            self::$storages[$name][$priority] = $callable;
        }

        ksort(self::$storages[$name]);

        return true;
    }

    /**
     * Belirtilen kancayı kaldırır
     * @param $name
     */
    public function remove($name)
    {
        if (isset(self::$storages[$name])) {
            unset(self::$storages[$name]);
        }
    }


    /**
     * Kanca daha önce eklenmiş mi kontrol eder
     * @param $name
     * @return bool
     */
    public function exists($name): bool
    {
        return isset(self::$storages[$name]);
    }

    /**
     * Tüm kancaların listesini döndürür
     * @return array
     */
    public function list(): array
    {
        return self::$storages;
    }

    /**
     * Bir kancayı çalıştırır
     * @param string $name
     * @param mixed $output
     * @return mixed|null
     */
    public function exec(string $name, $output)
    {
        if ($this->exists($name)) {

            foreach (self::$storages[$name] as $callable) {

                $output = call_user_func_array($callable, [$output]);
            }
        }

        return $output;
    }
}
