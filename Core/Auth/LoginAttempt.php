<?php 
/**
 * @Created 19.01.2021 14:47:24
 * @Project gorevyap
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class LoginAttempt
 * @package Core\Auth
 */


namespace Core\Auth;


use Cache;
use Core\Cache\CacheInterface;

trait LoginAttempt {

    protected bool $loginAttempt = true;
    protected int $loginAttemptMax = 3;
    protected int $loginAttemptTimeout = 60;


    /**
     * kullanıcı giriş deneme haklarını tüketmişse true döner
     * @param string $username
     * @param string $ip
     * @return bool
     */
    private function checkloginAttempt(string $username, string $ip):bool
    {
        if ($this->loginAttempt) {

            if (Cache::get('login_attempt_' . $username . $ip) >= $this->loginAttemptMax) {

                return true;
            }
        }

        return false;
    }

    /**
     * Giriş deneme sayısını arttırır
     * @param string $username
     * @param string $ip
     */
    private function setLoginAttempt(string $username, string $ip)
    {
        if ($this->loginAttempt) {

            $count = $this->cache()->get('login_attempt_' . $username . $ip);

            if ($count < $this->loginAttemptMax) {

                $this->cache()->set('login_attempt_' . $username . $ip, $count + 1, $this->loginAttemptTimeout);
            }
        }
    }


    /**
     * kullanıcı giriş haklarını sıfırlar
     * @param string $username
     * @param string $ip
     */
    protected function clearLoginAttempt(string $username, string $ip)
    {
        if ($this->loginAttempt) {
            $this->cache()->delete('login_attempt_' . $username . $ip);
        }
    }


    /**
     * @return CacheInterface
     */
    private function cache():CacheInterface
    {
        return app()->resolve(\Core\Cache\Cache::class);
    }
}
