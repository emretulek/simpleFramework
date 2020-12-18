<?php


namespace Core\Crypt;


use Exception;

class Crypt
{
    private string $secretKey;
    private string $crypt_algo;
    private string $hash_algo;

    /**
     * Crypt constructor.
     * @param string $secretKey
     * @param string $crypt_algo
     * @param string $hash_algo
     */
    public function __construct(string $secretKey, string $crypt_algo, string $hash_algo)
    {
        $this->secretKey = $secretKey;
        $this->crypt_algo = $crypt_algo;
        $this->hash_algo = $hash_algo;
    }


    /**
     * @param string $text
     * @return string
     * @throws Exception
     */
    public function encrypt(string $text):string
    {
        $iv_len = openssl_cipher_iv_length($this->crypt_algo);
        $iv = openssl_random_pseudo_bytes($iv_len);

        $encrypted = openssl_encrypt($text, $this->crypt_algo, $this->secretKey, 0, $iv);

        $json = json_encode(['encrypted' => $encrypted, 'iv' => base64_encode($iv), 'hash' => $this->hash($iv . $encrypted)]);

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
    public function decrypt(string $encodedText)
    {
        $json = base64_decode($encodedText);
        $encrypted = json_decode($json, true);
        $encrypted['iv'] = base64_decode($encrypted['iv']);

        if (!is_array($encrypted) || !isset($encrypted['encrypted'], $encrypted['iv'], $encrypted['hash'])) {

            throw new Exception("Şifreleme çözülemiyor.", E_WARNING);
        }
        if (!hash_equals($this->hash($encrypted['iv'].$encrypted['encrypted']), $encrypted['hash'])) {

            return false;
        }

        return openssl_decrypt($encrypted['encrypted'], $this->crypt_algo, $this->secretKey, 0, $encrypted['iv']);
    }


    /**
     * @param $veriable
     * @return string
     * @throws Exception
     */
    public function encryptSerial($veriable):string
    {
        $veriable = serialize($veriable);
        return $this->encrypt($veriable);
    }


    /**
     * @param string $encodedVeriable
     * @return mixed
     * @throws Exception
     */
    public function decryptSerial(string $encodedVeriable)
    {
        $veriable = $this->decrypt($encodedVeriable);
        return unserialize($veriable);
    }


    /**
     * @param $text
     * @return string
     */
    private function hash($text):string
    {
        return hash_hmac($this->hash_algo, $text, $this->secretKey, false);
    }
}
