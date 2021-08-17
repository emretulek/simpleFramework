<?php


namespace Core\Auth;


use Core\Cache\Cache;
use Core\Cache\CacheInterface;

trait Attempt
{

    protected bool $attempt = true;
    protected int $attemptMax = 3;
    protected int $attemptTimeout = 60;


    /**
     * kullanıcı giriş deneme haklarını tüketmişse true döner
     * @param string $string
     * @return bool
     */
    private function checkAttempt(string $string): bool
    {
        if ($this->attempt) {

            if ($this->cache()->get($this->hash($string)) >= $this->attemptMax) {

                return true;
            }
        }

        return false;
    }

    /**
     * Giriş deneme sayısını arttırır
     * @param string $string
     */
    private function setAttempt(string $string)
    {
        if ($this->attempt) {

            $count = $this->cache()->get($this->hash($string));

            if ($count < $this->attemptMax) {

                $this->cache()->set($this->hash($string), $count + 1, $this->attemptTimeout);
            }
        }
    }


    /**
     * kullanıcı giriş haklarını sıfırlar
     * @param string $string
     */
    protected function clearAttempt(string $string)
    {
        if ($this->attempt) {
            $this->cache()->delete($this->hash($string));
        }
    }


    /**
     * @param string $string
     * @return string
     */
    protected function hash(string $string)
    {
        return md5('attempt_'.$string.request()->ip());
    }

    /**
     * @return CacheInterface
     */
    private function cache(): CacheInterface
    {
        return app()->resolve(Cache::class);
    }
}
