<?php

namespace Core\Validation;

use Core\Language\Language;
use Core\Crypt\Hash;

/**
 * Class Filter
 * İstemci tarafından gönderilen verilerin filtrelenmesinde kullanılır.
 */
class Filter
{
    private $messages;
    private $params = [];
    private $required = false;
    private $input = NULL;
    private $key = NULL;
    private $error = [];




    /**
     * Filter constructor.
     * @param array $params elemanları filtrelenecek dizi
     * @param string $lang hataların döndürüleceği dil.
     */
    public function __construct(array $params, string $lang = 'tr')
    {
        $this->params = $params;
        $this->messages = Language::loadFile($lang, 'validation');
    }

    /**
     * Filtrelenecek dizi elemanını belirler
     *
     * @param $key
     * @param bool $required
     * @return $this
     */
    public function input($key, $required = false)
    {
        $this->key = $key;

        if (array_key_exists($key, $this->params)) {
            $this->input = trim($this->params[$key]);
            $this->is_required($required);
        }else{
            $this->input = null;
            $this->errorMessage('no_index');
        }

        return $this;
    }


    /**
     * @param $required
     */
    private function is_required($required)
    {
        $this->required = $required;

        if($required == true && empty($this->input)){
            $this->errorMessage('required');
        }
    }


    /**
     * Geçerli bir email adresi değilse hata mesajı oluşturur.
     *
     * @return $this
     */
    public function email()
    {
        if(!Valid::email($this->input)){
            $this->errorMessage('email');
        }

        return $this;
    }

    /**
     * Geçerli bir url adresi değilse hata mesajı oluşturur.
     *
     * @return $this
     */
    public function url()
    {
        if(!Valid::url($this->input)){
            $this->errorMessage('url');
        }

        return $this;
    }

    /**
     * Geçerli bir kullanıcı adı olup olmadığına bakar.
     * Filter::PATTERN de belirlenen desene uyamazsa hata mesajı oluşturur.
     *
     * @return $this
     */
    public function username()
    {
        if(!Valid::username($this->input)){
            $this->errorMessage('username', 4, 32);
        }

        return $this;
    }


    /**
     * Geçerli bir isim olup olmadığına bakar.
     * Filter::PATTERN de belirlenen desene uyamazsa hata mesajı oluşturur.
     *
     * @return $this
     */
    public function name()
    {
        if(!Valid::name($this->input)){
            $this->errorMessage('name', 2, 64);
        }

        return $this;
    }


    /**
     * Dosya adının kullanılabilir olup olmadığına bakar.
     * Filter::PATTERN de belirlenen desene uyamazsa hata mesajı oluşturur.
     *
     * @return $this
     */
    public function filename()
    {
        if(!Valid::filename($this->input)){
            $this->errorMessage('filename');
        }

        return $this;
    }


    /**
     * Geçerli bir şifre olup olmadığını kontrol eder, repassword belirtilmişse şifre uyumunuda kontrol eder.
     * Hata varsa mesaj oluşturur.
     *
     * @param string $repassword şifre uyumunun kontrol edileceği index.
     * @return $this
     */
    public function password(string $repassword = null)
    {
        if(Valid::length($this->input, 4, 16)) {

            if ($repassword !== null and array_key_exists($repassword, $this->params)) {

                if ($this->params[$repassword] != $this->input) {
                    $this->error[$this->key] = $this->messages['repassword'];
                }
            }

            $this->input = Hash::password($this->input);
        }else{
            $this->errorMessage('password', 4, 16);
        }

        return $this;
    }


    /**
     * İstenen uzunluk aralığında değilse hata döndürür
     *
     * @param null $min
     * @param null $max
     * @return $this
     */
    public function length($min = null, $max = null)
    {
        if(!Valid::length($this->input, $min, $max) && $max === null){
            $this->errorMessage('min_len', $min);
        }
        if(!Valid::length($this->input, $min, $max) && $min === null){
            $this->errorMessage('max_len', $max);
        }
        if(!Valid::length($this->input, $min, $max) && $min !== null && $max !== null){
            $this->errorMessage('between_len', $min, $max);
        }

        return $this;
    }


    /**
     * Float veri istenen aralıkta değilse hata döndürür.
     *
     * @param null $min
     * @param null $max
     * @return $this
     */
    public function float($min = null, $max = null)
    {
        if(!Valid::float($this->input, $min, $max) && $max === null){
            $this->errorMessage('min', $min);
        }
        if(!Valid::float($this->input, $min, $max) && $min === null){
            $this->errorMessage('max', $max);
        }
        if(!Valid::float($this->input, $min, $max) && $min !== null && $max !== null){
            $this->errorMessage('between', $min, $max);
        }

        $this->input = floatval($this->input);

        return $this;
    }


    /**
     * Integer veri istenen aralıkta değilse hata döndürür.
     *
     * @param null $min
     * @param null $max
     * @return $this
     */
    public function int($min = null, $max = null)
    {
        if(!Valid::int($this->input, $min, $max) && $max === null){
            $this->errorMessage('min', $min);
        }
        if(!Valid::int($this->input, $min, $max) && $min === null){
            $this->errorMessage('max', $max);
        }
        if(!Valid::int($this->input, $min, $max) && $min !== null && $max !== null){
            $this->errorMessage('between', $min, $max);
        }

        $this->input = intval($this->input);

        return $this;
    }


    /**
     * İstenen regex ile uyuşmazsa hata verir
     *
     * @param $pattern
     * @return $this
     */
    public function regex($pattern)
    {
        if(!preg_match($pattern, $this->input)){
            $this->errorMessage('regex', $pattern);
        }

        return $this;
    }


    /**
     * Sadece yazı karakterlerini ve alt çizgi kabul eder, diğer verileri temizler
     *
     * @param bool $unicode
     * @return $this
     */
    public function toAlpha($unicode = false)
    {
        $pattern = '/[^\w]/';
        $pattern .= $unicode ? 'u' : '';

        $this->input = preg_replace($pattern, '', $this->input);

        return $this;
    }


    /**
     * Sadece rakam kabul eder diğer karakterleri temizler
     *
     * @return $this
     */
    public function toNumber()
    {
        $this->input = preg_replace('/[^0-9]/', '', $this->input);

        return $this;
    }


    /**
     * Sadece yazı ve rakamları kabul eder diğer tüm karakterleri temizler
     *
     * @param bool $unicode
     * @return $this
     */
    public function toAlnum($unicode = false)
    {
        $pattern = '/[^0-9\w]/';
        $pattern .= $unicode ? 'u' : '';

        $this->input = preg_replace($pattern, '', $this->input);

        return $this;
    }

    /**
     * Tüm html taglarını temizler.
     * @param $allowed
     * @return $this
     */
    public function toText($allowed = null)
    {
        $this->input = strip_tags($this->input, $allowed);
        return $this;
    }


    /**
     * Html taglarını ASCII kodlarına dönüştürür.
     *
     * @return $this
     */
    public function toHtmlText()
    {
        $this->input = htmlentities($this->input, ENT_NOQUOTES);
        return $this;
    }



    /**
     * Türkçe karakterleri çevirip özel karakterleri silerek temiz bir url yapısı oluştutur.
     *
     * @return $this
     */
    public function toPermaLink()
    {
        $tr = array("Ç", "ç", "Ğ", "ğ", "İ", "ı", "Ö", "ö", "Ş", "ş", "Ü", "ü");
        $en = array("C", "c", "G", "g", "I", "i", "O", "o", "S", "s", "U", "u");

        $this->input = str_replace($tr, $en, $this->input);
        $this->input = strtolower($this->input);
        $this->input = preg_replace("/[^\w]/", "-", $this->input);
        $this->input = preg_replace("/[\-]{1,}/", "-", $this->input);
        $this->input = trim($this->input, "-");

        return $this;
    }


    /**
     * Hata mesajının başına ön ek ekler
     * @TODO Zorunlu alan. => Kullanıcı Adı: Zorunlu alan.
     * @param $label
     * @return $this
     */
    public function label($label)
    {
        if(isset($this->error[$this->key])){
            $this->error[$this->key] = '<b>'.$label.'</b> '.$this->error[$this->key];
        }

        return $this;
    }

    /**
     * Hata mesajını girilen hata mesajı ile değiştirir.
     *
     * @param string|null $error_message
     * @return $this
     */
    public function message(string $error_message)
    {
        if (isset($this->error[$this->key])) {
            $this->error[$this->key] = $error_message;
        }
        return $this;
    }


    /**
     * Filtre edilen veriyi döndürür.
     *
     * @return null
     */
    public function result()
    {
        if($this->required == false && empty($this->input)){
            unset($this->error[$this->key]);
        }else{
            $this->required = false;
        }

        return $this->input;
    }


    private function errorMessage($key, ...$args)
    {
        $this->error[$this->key] = vsprintf($this->messages[$key], $args);
    }

    /**
     * Oluşan hataları dizi olarak döndürür.
     *
     * @return array
     */
    public function error(): array
    {
        return $this->error;
    }
}

