<?php

namespace Core\Validation;

use DateTime;

class Valid
{
    /**
     * true, yes, on, ok, evet, tamam değerlerine true aksi halde false döndürür
     *
     * @param $input [true, yes, on, ok, evet, tamam]
     * @return bool
     */
    public static function bool($input)
    {
        return filter_var($input, FILTER_VALIDATE_BOOLEAN) ||
            mb_strtolower($input) == 'ok' ||
            mb_strtolower($input) == 'evet' ||
            mb_strtolower($input) == 'tamam';
    }

    /**
     * Float ise ve istenen aralıkta ise sayıyı aksi halde false döndürür
     * @param $input
     * @param ?int $min
     * @param ?int $max
     * @return false|float
     */
    public static function float($input, $min = null, $max = null)
    {
        if (($input = filter_var($input, FILTER_VALIDATE_FLOAT)) !== false) {

            if ($min === null && $max === null) {
                return (float)$input;
            }
            if ($min !== null && $max === null && $input >= $min) {
                return (float)$input;
            }
            if ($max !== null && $min === null && $input <= $max) {
                return (float)$input;
            }
            if ($min !== null && $max !== null && $input >= $min && $input <= $max) {
                return (float)$input;
            }
        }

        return false;
    }

    /**
     * Integer ise ve istenen aralıkta ise sayıyı aksi halde false döndürür
     * @param $input
     * @param ?int $min
     * @param ?int $max
     * @return false|int
     */
    public static function int($input, $min = null, $max = null)
    {
        if (($input = filter_var($input, FILTER_VALIDATE_INT)) !== false) {

            if ($min === null && $max === null) {
                return (int)$input;
            }
            if ($min !== null && $max === null && $input >= $min) {
                return (int)$input;
            }
            if ($max !== null && $min === null && $input <= $max) {
                return (int)$input;
            }
            if ($min !== null && $max !== null && $input >= $min && $input <= $max) {
                return (int)$input;
            }
        }

        return false;
    }

    /**
     * IPv4 ise ip adresini aksi halde false döndürür
     *
     * @param $input
     * @return false|string
     */
    public static function ipv4($input)
    {
        return filter_var($input, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

    /**
     * IPv6 ise ip adresini aksi halde false döndürür
     *
     * @param $input
     * @return false|string
     */
    public static function ipv6($input)
    {
        return filter_var($input, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    }

    /**
     * IP ise ip adresini aksi halde false döndürür
     *
     * @param $input
     * @return false|string
     */
    public static function ip($input)
    {
        return filter_var($input, FILTER_VALIDATE_IP);
    }

    /**
     * Mac adresi geçerli ise mac adresini aksi halde false döndürür
     *
     * @param $input
     * @return false|string
     */
    public static function mac($input)
    {
        return filter_var($input, FILTER_VALIDATE_MAC);
    }

    /**
     * Url geçerli ise url aksi halde false döndürür
     *
     * @param $input
     * @return false|string
     */
    public static function url($input)
    {
        return filter_var($input, FILTER_VALIDATE_URL);
    }

    /**
     * Domain geçerli ise domain aksi halde false döndürür
     *
     * @param $input
     * @return false|string
     */
    public static function domain($input)
    {
        return filter_var($input, FILTER_VALIDATE_DOMAIN);
    }

    /**
     * Email adresi geçerli ise email aksi halde false döndürür
     *
     * @param $input
     * @return false|string
     */
    public static function email($input)
    {
        return filter_var($input, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Telefon numarası geçerli ise numarayı aksi halde false döndürür
     *
     * @param $input
     * @return false|string
     */
    public static function phone($input)
    {
        $phonNumber = preg_replace('#[^0-9]#', '', $input);

        return self::length($input, 9, 15) ? $phonNumber : false;
    }

    /**
     * Girilen tarih format ile uyuşursa tarihi aksi halde false döndürür
     *
     * @param $date
     * @param string $format
     * @return false|string
     */
    public static function date($date, $format = "Y-m-d H:i:s")
    {
        $dateTime = DateTime::createFromFormat($format, $date);

        return $dateTime && $dateTime->format($format) == $date ? $date : false;
    }

    /**
     * Kullanıcı adı geçerli ise kullanıcı adı aksi halde false döndürür
     *
     * @param $username
     * @param string $pattern
     * @return false|string
     */
    public static function username($username, $pattern = '/^[\w]{4,64}$/i')
    {
        return preg_match($pattern, $username) ? (string)$username : false;
    }


    /**
     * Gerçek bir isim ise ismi aksi halde false döndürür
     *
     * @param $name
     * @param string $pattern
     * @return false|string
     */
    public static function name($name, $pattern = '/^(?:\p{L}\s?){2,64}$/iu')
    {
        return preg_match($pattern, $name) ? (string)$name : false;
    }

    /**
     * Geçerli bir dosya adı ise dosya adı aksi halde false döndürür
     *
     * @param $filename
     * @param string $pattern
     * @return false|string
     */
    public static function filename($filename, $pattern = '/^[^\s\.][\w\-\.\s]{4,255}$/iu')
    {
        return preg_match($pattern, $filename) ? (string)$filename : false;
    }


    /**
     * Uzunluk istenen değerler arasında ise uzunluğu aksi halde false döndürür
     *
     * @param $input
     * @param int|null $min
     * @param int|null $max
     * @return false|int
     */
    public static function length($input, int $min = 1, int $max = null)
    {
        $length = (int)mb_strlen($input);

        if ($min != null && $max == null && $length >= $min) {
            return $length;
        }
        if ($max != null && $min == null && $length <= $max) {
            return $length;
        }
        if ($min != null && $max != null && $length >= $min && $length <= $max) {
            return $length;
        }

        return false;
    }


    /**
     * Yazı karakteri dışında karakter içermiyorsa değeri aksi halde false döndürür
     * @param $input
     * @param bool $unicode
     * @return false|string
     */
    public static function alpha($input, $unicode = false)
    {
        $pattern = '/^[a-z]+$/i';
        $pattern = $unicode ? '/^[\p{L}]+$/i' : $pattern;

        return preg_match($pattern, $input) ? $input : false;
    }


    /**
     * Rakam dışında karakter içermiyorsa sayıyı aksi halde false döndürür
     *
     * @param $input
     * @return false|string
     */
    public static function number($input)
    {
        return preg_match('/^[0-9]+$/', $input) ? $input : false;
    }


    /**
     * Yazı karakteri ve rakam dışında karakter içermiyorsa değeri aksi halde false döndürür
     * @param $input
     * @param bool $unicode
     * @return false|string
     */
    public static function alnum($input, $unicode = false)
    {
        $pattern = '/[^a-z0-9]/i';
        $pattern = $unicode ? '/^[\p{L}0-9]+$/i' : $pattern;

        return preg_match($pattern, $input) ? $input : false;
    }


    /**
     * Kredi kart numarasının geçerli olup olmadığını denetler geçerli ise kart numarası aksi halde false döndürür
     * @param string $cardNumber
     * @return false|string
     */
    public static function creditCard(string $cardNumber)
    {
        $cleanNumbers = str_split(preg_replace("/[^\d]/", "", $cardNumber));
        $numbers = $cleanNumbers;
        $lastNumber = array_pop($numbers);
        $numbersDesc = array_reverse($numbers);
        $numbersDescDouble = [];

        foreach ($numbersDesc as $key => $item) {
            if ($key % 2 == 0) {
                $newItem = $item * 2;
                $numbersDescDouble[] = $newItem > 9 ? $newItem - 9 : $newItem;
            } else {
                $numbersDescDouble[] = $item;
            }
        }

        $sum = array_sum($numbersDescDouble);

        return 10 - ($sum % 10) == $lastNumber ? implode($cleanNumbers) : false;
    }


    /**
     * TC kimlik no algoritma kontrol
     * @param string $input
     * @return false|string
     */
    public static function tcNo(string $input)
    {
        if (strlen($input) == 11 && $input[0] != 0) {

            $tcno = str_split($input);
            $no11 = array_pop($tcno);
            $no10 = array_pop($tcno);

            $no10_base = ($tcno[0] + $tcno[2] + $tcno[4] + $tcno[6] + $tcno[8]) * 7 - ($tcno[1] + $tcno[3] + $tcno[5] + $tcno[7]);
            $no10_will = substr($no10_base, -1);

            $no11_base = array_sum($tcno) + $no10_will;
            $no11_will = substr($no11_base, -1);

            if ($no10 == $no10_will && $no11 == $no11_will) {

                return $input;
            }
        }

        return false;
    }
}
