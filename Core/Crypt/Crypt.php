<?php


namespace Core\Crypt;


use Core\Config\Config;

class Crypt
{
    private static $secretKey;
    private static $algorithm;
    private static $iv;

    /**
     * Crypt constructor.
     */
    private static function init()
    {
        self::$secretKey = Config::get('app.app_key');
        self::$algorithm = Config::get('app.encrypt_algo');

        $iv_len = openssl_cipher_iv_length(self::$algorithm);
        self::$iv = openssl_random_pseudo_bytes($iv_len);
    }


    /**
     * @param string $text
     * @return false|string
     */
    public static function encrypt(string $text)
    {
        self::init();
        return openssl_encrypt($text, self::$algorithm, self::$secretKey,0, self::$iv);
    }


    /**
     * @param string $encodedText
     * @return false|string
     */
    public static function decrypt(string $encodedText)
    {
        self::init();
        return openssl_decrypt($encodedText, self::$algorithm, self::$secretKey,0, self::$iv);
    }


    /**
     * dizi veya nesne şifrelemek için serialize edilir
     * @param object|array $veriable
     * @return object|array|false
     */
    public static function encryptSerial($veriable)
    {
        self::init();
        $veriable = serialize($veriable);
        return openssl_encrypt($veriable, self::$algorithm, self::$secretKey,0, self::$iv);
    }


    /**
     * dizi veya nesne şifresini açıp unserialize eder
     * @param string $encodedVeriable
     * @return object|array|false
     */
    public static function decryptSerial(string $encodedVeriable)
    {
        self::init();
        $veriable = openssl_decrypt($encodedVeriable, self::$algorithm, self::$secretKey,0, self::$iv);
        return unserialize($veriable);
    }
}
