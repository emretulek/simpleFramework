<?php

namespace Core\Session;

/**
 * Class Session
 * Kolay session yönetimi sağlar
 */
class Session
{

    /**
     * Oturum ayarlarını yükleyerek oturumu başlatır.
     *
     * @param int $lifetime Oturumun ömrünü beliler.
     * @param string $path oturum verilerinin kaydedileceği dizin.
     * @param null $domain oturumun geçerli olduğu alan
     * @param bool $secure true değeri atanırsa https etki alanında çalışır.
     * @param bool $httponly oturum verilerine istemci tarafından erişim kısıtlanır.
     */
    public static function start($lifetime = 0, $path = '/', $domain = null, $secure = false, $httponly = true)
    {
        session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);
        session_name("SID");
        session_start();
        self::tmpClear();
    }


    /**
     * Yeni bir oturum verisi oluşturur.
     *
     * @param string $name nokta ile birleşitirilmiş session indexi (index1.index2)
     * @param string $value
     * @return bool
     */
    public static function set(string $name, string $value)
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            dot_aray_set($_SESSION, $name, $value);
            return true;
        }
        return false;
    }


    /**
     * Adı girilen oturum verisini döndürür. Bulamazsa false döner.
     *
     * @param string $name nokta ile birleşitirilmiş session indexi (index1.index2)
     * @return mixed
     */
    public static function get(string $name)
    {
        return dot_aray_get($_SESSION, $name);
    }


    /**
     * Adı girilen oturum verisini siler.
     * @param string $name nokta ile birleşitirilmiş session indexi (index1.index2)
     * @return bool
     */
    public static function remove(string $name)
    {
        return dot_array_del($_SESSION, $name);
    }


    /**
     * Session::set methodu ile benzer çalışır, farkı verinin bir kez taşınıp otomatik silinmesidir.
     * Session::set gibi dizi derinlik özelliği yoktur.
     *
     * @param string $name oluşturulan oturum verisinin adı
     * @param string $value
     * @return bool
     */
    public static function tmpSet(string $name, string $value)
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['__TEMPRORY__']['__TIMES__'][$name] = 0;
            $_SESSION['__TEMPRORY__'][$name] = $value;
            return true;
        }
        return false;
    }

    /**
     * Session::get methodu ile benzer çalışır, farkı sadece Session::tmpSet ile oluşturulan verileri erişmesidir.
     *
     * @param string $name nokta ile birleşitirilmiş session indexi (index1.index2)
     * @return mixed
     */
    public static function tmpGet(string $name)
    {
        return $_SESSION['__TEMPRORY__'][$name];
    }


    /**
     * Session::tmpSet medhoduyla oluşturulan verileri bir sonraki sayfada siler.
     */
    public static function tmpClear()
    {
        if (isset($_SESSION['__TEMPRORY__'])) {

            foreach ($_SESSION['__TEMPRORY__']['__TIMES__'] as $key => $val) {

                if ($val == 1) {
                    unset($_SESSION['__TEMPRORY__']['__TIMES__'][$key]);
                    unset($_SESSION['__TEMPRORY__'][$key]);
                } else {
                    $_SESSION['__TEMPRORY__']['__TIMES__'][$key] = 1;
                }
            }
        }
    }


    /**
     * Tüm oturum bilgilerini siler.
     */
    public static function destroy()
    {
        session_destroy();
    }
}
