<?php

namespace Core\Validation;

use Core\Config\Config;
use Core\Language\Language;
use Core\Crypt\Hash;

/**
 * Class Filter
 * İstemci tarafından gönderilen verilerin filtrelenmesinde kullanılır.
 */
class Filter
{
    private array $params;
    private bool $required = false;
    private ?string $input = NULL;
    private ?string $key = NULL;
    private array $error = [];

    private array $messages = [
        'no_index' => 'Geçersiz index',
        'required' => 'Zorunlu alan.',
        'email' => 'Geçersiz E-posta adresi.',
        'url' => 'Hatalı web adresi.',
        'username' => 'Kullanıcı adında harf ve rakam kullanın. En az %s en fazla %s karakter.',
        'name' => 'Ad ve Soyad alanlarında sadece harf kullanılabilir, en az %s, en fazla %s karakter.',
        'filename' => 'Uygunsuz dosya adı.',
        'password' => 'Şifreniz en az %s en fazla %s karakter uzunluğunda olmalı',
        'repassword' => 'Şifreleriniz uyuşmuyor.',
        'min_len' => 'En az %s karakter kullanın.',
        'max_len' => 'En fazla %s karakter kullanın.',
        'between_len' => 'En az %s, en fazla %s karakter kullanın.',
        'min' => 'En az %s olmalı.',
        'max' => 'En fazla %s olmalı.',
        'between' => 'En az %s, en fazla %s arasında bir değer kullanın.',
        'regex' => 'İstenen desene %s uymalısınız.',
        'ip' => 'Geçersiz ip adresi.',
        'ipv4' => 'Geçersiz ipv4 adresi.',
        'ipv6' => 'Geçersiz ipv6 adresi.',
        'mac' => 'Geçersiz mac adresi.',
        'domain' => 'Geçersiz domain.',
        'phone' => 'Geçersiz telefon numarası.',
        'dateFormat' => 'Geçersiz tarih formatı.',
        'creditCard' => 'Geçersiz kredi kartı numarası.',
        'tcNo' => 'Geçersiz TC kimlik numarası.',
    ];

    /**
     * Filter constructor.
     * @param array $params elemanları filtrelenecek dizi
     * @param bool $lang hataların döndürüleceği dil otomatik belirlenir
     */
    public function __construct(array $params, bool $lang = true)
    {
        $this->params = $params;
        $this->messages = $lang ? Language::translate("validation") : $this->messages;
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
        } else {
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

        if ($required == true && empty($this->input)) {
            $this->errorMessage('required');
        }
    }

    /**
     * Float veri istenen aralıkta değilse hata döndürür.
     * @param null $min
     * @param null $max
     * @return $this
     */
    public function float($min = null, $max = null)
    {
        if (!Valid::float($this->input, $min, $max) && $max === null) {
            $this->errorMessage('min', $min);
        }
        if (!Valid::float($this->input, $min, $max) && $min === null) {
            $this->errorMessage('max', $max);
        }
        if (!Valid::float($this->input, $min, $max) && $min !== null && $max !== null) {
            $this->errorMessage('between', $min, $max);
        }

        $this->input = floatval($this->input);

        return $this;
    }

    /**
     * Integer veri istenen aralıkta değilse hata döndürür.
     * @param null $min
     * @param null $max
     * @return $this
     */
    public function int($min = null, $max = null)
    {
        if (!Valid::int($this->input, $min, $max) && $max === null) {
            $this->errorMessage('min', $min);
        }
        if (!Valid::int($this->input, $min, $max) && $min === null) {
            $this->errorMessage('max', $max);
        }
        if (!Valid::int($this->input, $min, $max) && $min !== null && $max !== null) {
            $this->errorMessage('between', $min, $max);
        }

        $this->input = intval($this->input);

        return $this;
    }

    /**
     * IPv4 adres kontrolü yapar
     * @return $this
     */
    public function ipv4()
    {
        if (!Valid::ipv4($this->input)) {
            $this->errorMessage('ipv4');
        }

        return $this;
    }

    /**
     * IPv6 adres kontrolü yapar
     * @return $this
     */
    public function ipv6()
    {
        if (!Valid::ipv6($this->input)) {
            $this->errorMessage('ipv6');
        }

        return $this;
    }

    /**
     * IP adres kontrolü yapar
     * @return $this
     */
    public function ip()
    {
        if (!Valid::ipv6($this->input)) {
            $this->errorMessage('ip');
        }

        return $this;
    }

    /**
     * Mac adres kontrolü yapar
     * @return $this
     */
    public function mac()
    {
        if (!Valid::mac($this->input)) {
            $this->errorMessage('mac');
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
        if (!Valid::url($this->input)) {
            $this->errorMessage('url');
        }

        return $this;
    }

    /**
     * Geçerli bir domain değilse hata mesajı oluşturur.
     *
     * @return $this
     */
    public function domain()
    {
        if (!Valid::domain($this->input)) {
            $this->errorMessage('domain');
        }

        return $this;
    }

    /**
     * Geçerli bir email adresi değilse hata mesajı oluşturur.
     *
     * @return $this
     */
    public function email()
    {
        if (!Valid::email($this->input)) {
            $this->errorMessage('email');
        }

        return $this;
    }


    /**
     * 0-9+\s- dışındaki karakterleri temizler
     * Geçerli bir telefon numarası değilse hata mesajı oluşturur.
     * @return $this
     */
    public function phone()
    {
        $this->input = preg_replace("/[^0-9+\-]/", "", $this->input);
        $this->input = preg_replace("/[\-]{1,}/", "-", $this->input);
        $this->input = preg_replace("/[+]{1,}/", "+", $this->input);

        if (!Valid::phone($this->input)) {
            $this->errorMessage('phone');
        }

        return $this;
    }


    /**
     * Tarih istenen biçimde değil ise hata mesajı oluşturur
     * @param string $format
     * @return $this
     */
    public function date(string $format = "Y-m-d H:i:s")
    {
        if (!$date = Valid::date($this->input, $format)) {
            $this->errorMessage('dateFormat');
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
        if (!Valid::username($this->input)) {
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
        if (!Valid::name($this->input)) {
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
        if (!Valid::filename($this->input)) {
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
        if (Valid::length($this->input, 4, 16)) {

            if ($repassword !== null and array_key_exists($repassword, $this->params)) {

                if ($this->params[$repassword] != $this->input) {
                    $this->errorMessage('repassword');
                }
            }

            $this->input = Hash::password($this->input);
        } else {
            $this->errorMessage('password', 4, 16);
        }

        return $this;
    }

    /**
     * Geçerli bir kredi kartı numarası değilse hata mesajı oluşturur
     * @return $this
     */
    public function creditCard()
    {
        if (!Valid::creditCard($this->input)) {
            $this->errorMessage('creditCard');
        }

        return $this;
    }


    /**
     * TC kimlik no algoritmasını geçemezse hata mesajı oluşturur
     * @return $this
     */
    public function tcNo()
    {
        if (!Valid::tcNo($this->input)) {
            $this->errorMessage('tcNo');
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
        if (!Valid::length($this->input, $min, $max) && $max === null) {
            $this->errorMessage('min_len', $min);
        }
        if (!Valid::length($this->input, $min, $max) && $min === null) {
            $this->errorMessage('max_len', $max);
        }
        if (!Valid::length($this->input, $min, $max) && $min !== null && $max !== null) {
            $this->errorMessage('between_len', $min, $max);
        }

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
        if (!preg_match($pattern, $this->input)) {
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
    public function toText(array $allowed = null)
    {
        $this->input = strip_tags($this->input, $allowed);
        return $this;
    }


    /**
     * Maksimum 1 boşluk karakteri
     * @return $this
     */
    public function spaceOne()
    {
        $this->input = preg_replace("/\s+/u", " ", $this->input);
        return $this;
    }

    /**
     * Html taglarını ASCII kodlarına dönüştürür.
     *
     * @return $this
     */
    public function toHtmlEntity()
    {
        $this->input = htmlentities($this->input, ENT_NOQUOTES, "UTF-8");
        return $this;
    }


    /**
     * Richtext filtreleme
     * @link https://stackoverflow.com/a/1741568
     * @return $this
     */
    public function toSecureHtml()
    {
        // Fix &entity\n;
        $data = str_replace(array('&amp;', '&lt;', '&gt;'), array('&amp;amp;', '&amp;lt;', '&amp;gt;'), $this->input);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2noscript', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding', $data);

        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

        do {
            // Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        } while ($old_data !== $data);

        $this->input = $data;

        return $this;
    }


    /**
     * Türkçe karakterleri çevirip özel karakterleri silerek temiz bir url yapısı oluştutur.
     * @param string $allowed
     * @return $this
     */
    public function toPermaLink(string $allowed = null)
    {
        $tr = array("Ç", "ç", "Ğ", "ğ", "İ", "ı", "Ö", "ö", "Ş", "ş", "Ü", "ü");
        $en = array("C", "c", "G", "g", "I", "i", "O", "o", "S", "s", "U", "u");

        $this->input = str_replace($tr, $en, $this->input);
        $this->input = strtolower($this->input);
        $this->input = preg_replace("/[^\w" . preg_quote($allowed) . "]/", "-", $this->input);
        $this->input = preg_replace("/[\-]{1,}/", "-", $this->input);
        $this->input = trim($this->input, "-");

        return $this;
    }


    /**
     * Hata mesajının başına ön ek ekler
     *
     * @param $label
     * @return $this
     */
    public function label($label)
    {
        if (isset($this->error[$this->key])) {
            $this->error[$this->key] = '<b>' . $label . '</b> ' . $this->error[$this->key];
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
        if ($this->required == false && empty($this->input)) {
            unset($this->error[$this->key]);
        } else {
            $this->required = false;
        }

        return $this->input;
    }


    private function errorMessage($key, ...$args)
    {
        if (array_key_exists($key, $this->messages)) {
            $this->error[$this->key] = vsprintf($this->messages[$key], $args);
        } else {
            $this->error[$this->key] = "[$key] belirlenmemiş hata mesajı.";
        }
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

