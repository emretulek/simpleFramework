<?php 
/**
 * @Created 14.12.2020 19:32:28
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class Crypt
 * @package Core\Facades
 */


namespace Core\Facades;

/**
 * @see \Core\Crypt\Crypt::encrypt()
 * @method static string encrypt(string $text)
 * ------------------------------------------------------------
 * @see \Core\Crypt\Crypt::decrypt()
 * @method static mixed decrypt(string $encodedText)
 * ------------------------------------------------------------
 * @see \Core\Crypt\Crypt::encryptSerial()
 * @method static string encryptSerial($veriable)
 * ------------------------------------------------------------
 * @see \Core\Crypt\Crypt::decryptSerial()
 * @method static mixed decryptSerial(string $encodedVeriable)
 * ------------------------------------------------------------
 * @mixin \Core\Crypt\Crypt
 * @see \Core\Crypt\Crypt
 */

class Crypt extends Facade {

    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \Core\Crypt\Crypt::class;
    }
}
