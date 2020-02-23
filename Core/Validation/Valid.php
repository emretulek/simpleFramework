<?php

namespace Core\Validation;

use DateTime;

Class Valid
{

    /**
     * TODO true, yes, on, ok, evet, tamam değerlerine true aksi halde false döndürür
     *
     * @param $input [true, yes, on, ok, evet, tamam]
     * @return bool
     */
    public static function bool($input)
    {
        return filter_var($input, FILTER_VALIDATE_BOOLEAN) ||
            strtolower($input) == 'ok' ||
            strtolower($input) == 'evet' ||
            strtolower($input) == 'tamam';
    }

    /**
     * Float ise sayıyı değilse false döndürür
     *
     * @param $input
     * @param int $min
     * @param int $max
     * @return bool
     */
    public static function float($input, $min = null, $max = null)
    {
        if (($input = filter_var($input, FILTER_VALIDATE_FLOAT)) !== false) {

            if ($min === null && $max === null) {
                return true;
            }
            if ($min !== null && $max === null && $input >= $min) {
                return true;
            }
            if ($max !== null && $min === null && $input <= $max) {
                return true;
            }
            if ($min !== null && $max !== null && $input >= $min && $input <= $max) {
                return true;
            }
        }

        return false;
    }

    /**
     * Integer ise ve istenen aralıkta ise sayıyı aksi halde false döndürür
     *
     * @param $input
     * @param int $min
     * @param int $max
     * @return bool
     */
    public static function int($input, $min = null, $max = null)
    {
        if (($input = filter_var($input, FILTER_VALIDATE_INT)) !== false) {

            if ($min === null && $max === null) {
                return true;
            }
            if ($min !== null && $max === null && $input >= $min) {
                return true;
            }
            if ($max !== null && $min === null && $input <= $max) {
                return true;
            }
            if ($min !== null && $max !== null && $input >= $min && $input <= $max) {
                return true;
            }
        }

        return false;
    }

    /**
     * IPv4 ise true aksi halde false döndürür
     *
     * @param $input
     * @return mixed
     */
    public static function ipv4($input)
    {
        return filter_var($input, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * IPv6 ise true aksi halde false döndürür
     *
     * @param $input
     * @return bool
     */
    public static function ipv6($input)
    {
        return filter_var($input, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * Mac adresi geçerli ise true aksi halde false döndürür
     *
     * @param $input
     * @return bool
     */
    public static function mac($input)
    {
        return filter_var($input, FILTER_VALIDATE_MAC) !== false;
    }

    /**
     * Url geçerli ise true aksi halde false döndürür
     *
     * @param $input
     * @return bool
     */
    public static function url($input)
    {
        return filter_var($input, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Domain ise true aksi halde false döndürür
     *
     * @param $input
     * @return bool
     */
    public static function domain($input)
    {
        return filter_var($input, FILTER_VALIDATE_DOMAIN) !== false;
    }

    /**
     * Email adresi geçerli ise true aksi halde false döndürür
     *
     * @param $input
     * @return bool
     */
    public static function email($input)
    {
        return filter_var($input, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Telefon numarası geçerli ise true aksi halde false döndürür
     *
     * @param $phonNumber
     * @return bool
     */
    public static function phone($phonNumber)
    {
        $input = preg_replace('#[^0-9]#', '', $phonNumber);

        if (9 < strlen($input) && strlen($input) < 15) {

            return true;
        }
        return false;
    }

    /**
     * Girilen tarih format ile uyuşursa true aksi halde false döndürür
     *
     * @param $date
     * @param string $format
     * @return bool
     */
    public static function date($date, $format = "Y-m-d H:i:s")
    {
        $dateTime = DateTime::createFromFormat($format, $date);

        return $dateTime && $dateTime->format($format) == $date;
    }

    /**
     * Kullanıcı adı geçerli ise true aksi halde false döndürür
     *
     * @param $username
     * @param string $pattern
     * @return false|int
     */
    public static function username($username, $pattern = '/^[\w]{4,32}$/i')
    {
        return preg_match($pattern, $username);
    }


    /**
     * Gerçek bir isim ise true aksi halde false döndürür
     *
     * @param $name
     * @param string $pattern
     * @return false|int
     */
    public static function name($name, $pattern = '/^[\w\s]{2,64}$/iu')
    {
        return preg_match($pattern, $name);
    }

    /**
     * Geçerli bir dosya adı ise true aksi halde false döndürür
     *
     * @param $filename
     * @param string $pattern
     * @return false|int
     */
    public static function filename($filename, $pattern = '/^[\w\-\.]{4,255}$/iu')
    {
        return preg_match($pattern, $filename);
    }


    /**
     * Uzunluk istenen değerler arasında ise true aksi halde false döndürür
     *
     * @param $input
     * @param int $min
     * @param int|null $max
     * @return bool
     */
    public static function length($input, $min = 1, $max = null)
    {
        $length = mb_strlen($input);

        if ($min != null && $max == null && $length >= $min) {
            return true;
        }
        if ($max != null && $min == null && $length <= $max) {
            return true;
        }
        if ($min != null && $max != null && $length >= $min && $length <= $max) {
            return true;
        }

        return false;
    }


    /**
     * Yazı karakteri dışında karakter içermiyorsa true aksi halde false döndürür
     * @param $input
     * @param bool $unicode
     * @return false
     */
    public static function alpha($input, $unicode = false)
    {
        $pattern = '/^[\w\s]+$/';
        $pattern .= $unicode ? 'u' : '';

        return preg_match($pattern, $input) > 0;
    }


    /**
     * Rakam dışında karakter içermiyorsa true aksi halde false döndürür
     *
     * @param $input
     * @return false
     */
    public static function number($input)
    {
        return preg_match('/^[0-9]+$/', $input) > 0;
    }


    /**
     * Yazı karakteri ve rakam dışında karakter içermiyorsa true aksi halde false döndürür
     *
     * @param $input
     * @param bool $unicode
     * @return false
     */
    public function alnum($input, $unicode = false)
    {
        $pattern = '/[^0-9\w\s]/';
        $pattern .= $unicode ? 'u' : '';

        return preg_match($pattern, '', $input) > 0;
    }
}
