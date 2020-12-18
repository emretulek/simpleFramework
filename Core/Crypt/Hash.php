<?php


namespace Core\Crypt;


class Hash
{

    private string $secretKey;
    private string $hash_algo;
    private $password_hash;

    /**
     * Hash constructor.
     * @param string $secretKey
     * @param string $hash_algo
     * @param $password_hash
     */
    public function __construct(string $secretKey, string $hash_algo, $password_hash)
    {
        $this->secretKey = $secretKey;
        $this->hash_algo = $hash_algo;
        $this->password_hash = $password_hash;
    }


    /**
     * @param string $text
     * @param null $key
     * @param null $algo
     * @return string
     */
    public function makeWithKey(string $text, $key = null, $algo = null):string
    {
        $key = $key ?? $this->secretKey;
        $algo = $algo ?? $this->hash_algo;

        return hash_hmac($algo, $text, $key, false);
    }


    /**
     * hash üretir
     *
     * @param string $text
     * @param null $algo
     * @return string
     */
    public function make(string $text, $algo = null):string
    {
        $algo = $algo ?? $this->hash_algo;

        return hash($algo, $text, false);
    }


    /**
     * şifre için hash üretir
     *
     * @param string $password
     * @return false|string|null
     */
    public function password(string $password)
    {
        if ($this->password_hash === true) {
            return password_hash($password, PASSWORD_BCRYPT);
        }

        return hash($this->hash_algo, $password, false);
    }


    /**
     * şifre ile hash karşılaştırılır, doğru ise password hash değeri yanlış ise false döner,
     * ayrıca yeniden hash alınması gerekiyorsa hash değeri yenilenir.
     *
     * @param string $password
     * @param string $hashedPassword
     * use_algo false ise php nin password_hash fonksiyonu kullanılır
     * @return string|false
     */
    public function passwordCheck(string $password, string $hashedPassword)
    {
        if ($this->password_hash === true) {
            if ($hashedPassword = password_verify($password, $hashedPassword)) {
                return $this->passwordRehash($password, $hashedPassword);
            }
        }else {
            if(hash($this->password_hash, $password, false) == $hashedPassword){
                return $hashedPassword;
            }
        }

        return false;
    }


    /**
     * yeni hash üretilmesi gerekiyorsa üretir gerekmiyorsa false döndürür
     *
     * @param $password
     * @param $hashedPassword
     * @return string
     */
    public function passwordRehash(string $password, string $hashedPassword):string
    {
        if (password_needs_rehash($hashedPassword, PASSWORD_BCRYPT)) {
            return password_hash($password, PASSWORD_BCRYPT);
        }

        return $hashedPassword;
    }
}
