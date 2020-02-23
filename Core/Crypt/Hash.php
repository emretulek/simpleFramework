<?php


namespace Core\Crypt;


use Core\Config\Config;

class Hash
{

    private static $secretKey;
    private static $algorithm;
    private static $password_hash = true;


    /**
     * hash sınıfı için gerekli ayarları yükler
     */
    private static function init()
    {
        self::$secretKey = Config::get('app.app_key');
        self::$algorithm = Config::get('app.hash_algo');
        self::$password_hash = Config::get('app.password_hash');
    }


    /**
     * api_key kullanarak hash üretir
     *
     * @param string $text
     * @return string
     */
    public static function makeWithKey(string $text)
    {
        self::init();
        return hash_hmac ( self::$algorithm , $text , self::$secretKey, false );
    }


    /**
     * hash üretir
     *
     * @param string $text
     * @return string
     */
    public static function make(string $text)
    {
        self::init();
        return hash( self::$algorithm , $text, false );
    }


    /**
     * şifre için hash üretir
     *
     * @param string $password
     * @return false|string|null
     */
    public static function password(string $password)
    {
        self::init();
        if(self::$password_hash) {
            return password_hash($password, PASSWORD_BCRYPT);
        }else{
            return hash(self::$algorithm, $password, false);
        }
    }


    /**
     * şifre ile hash karşılaştırılır
     *
     * @param string $password
     * @param string $hashedPassword
     * @return bool
     */
    public static function passwordCheck(string $password, string $hashedPassword)
    {
        self::init();
        if(self::$password_hash) {
            return password_verify($password, $hashedPassword);
        }else{
            return hash(self::$algorithm, $password, false) == $hashedPassword;
        }
    }


    /**
     * yeni hash üretilmesi gerekiyorsa üretir gerekmiyorsa false döndürür
     *
     * @param $password
     * @param $hashedPassword
     * @return bool|false|string|null
     */
    public static function passwordRehash(string $password, string $hashedPassword)
    {

        if(self::$password_hash && self::passwordCheck($password, $hashedPassword)) {
            if (password_needs_rehash($hashedPassword, PASSWORD_BCRYPT)) {
                return password_hash($password, PASSWORD_BCRYPT);
            }
        }

        return false;
    }
}
