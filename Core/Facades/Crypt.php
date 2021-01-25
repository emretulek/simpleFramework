<?php

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
class Crypt extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \Core\Crypt\Crypt::class;
    }
}
