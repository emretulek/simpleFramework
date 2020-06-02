<?php


namespace Core\Crypt;


use Core\Config\Config;
use Exception;

class Crypt
{
    private static $secretKey;
    private static $algorithm;

    /**
     * Crypt constructor.
     */
    private static function init()
    {
        self::$secretKey = Config::get('app.app_key');
        self::$algorithm = Config::get('app.encrypt_algo');
    }


    /**
     * @param string $text
     * @return string
     * @throws Exception
     */
    public static function encrypt(string $text)
    {
        self::init();
        $iv_len = openssl_cipher_iv_length(self::$algorithm);
        $iv = openssl_random_pseudo_bytes($iv_len);

        $encrypted = openssl_encrypt($text, self::$algorithm, self::$secretKey, 0, $iv);

        $json = json_encode(['encrypted' => $encrypted, 'iv' => base64_encode($iv), 'hash' => Hash::makeWithKey($iv . $encrypted)]);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception(json_last_error(), E_WARNING);
        }

        return base64_encode($json);
    }


    /**
     * @param string $encodedText
     * @return false|string
     * @throws Exception
     */
    public static function decrypt(string $encodedText)
    {
        self::init();
        $json = base64_decode($encodedText);
        $encrypted = json_decode($json, true);
        $encrypted['iv'] = base64_decode($encrypted['iv']);

        if (!is_array($encrypted) || !isset($encrypted['encrypted'], $encrypted['iv'], $encrypted['hash'])) {

            throw new Exception("Şifreleme çözülemiyor.", E_WARNING);
        }
        if (!hash_equals(Hash::makeWithKey($encrypted['iv'].$encrypted['encrypted']), $encrypted['hash'])) {

            throw new Exception("Güvenlik gereği şifre çözülmedi.", E_WARNING);
        }

        return openssl_decrypt($encrypted['encrypted'], self::$algorithm, self::$secretKey, 0, $encrypted['iv']);
    }


    /**
     * @param $veriable
     * @return string
     * @throws Exception
     */
    public static function encryptSerial($veriable)
    {
        self::init();
        $veriable = serialize($veriable);
        return self::encrypt($veriable);
    }


    /**
     * @param string $encodedVeriable
     * @return mixed
     * @throws Exception
     */
    public static function decryptSerial(string $encodedVeriable)
    {
        self::init();
        $veriable = self::decrypt($encodedVeriable);
        return unserialize($veriable);
    }
}
