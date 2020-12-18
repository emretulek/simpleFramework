<?php 
/**
 * @Created 14.12.2020 19:38:11
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class Hash
 * @package Core\Facades
 */


namespace Core\Facades;

/**
 * @see \Core\Crypt\Hash::make()
 * @method static string make(string $text, $algo = null)
 * ----------------------------------------------------------------------------------
 * @see \Core\Crypt\Hash::makeWithKey()
 * @method static string makeWithKey(string $text, $key = null, $algo = null)
 * ----------------------------------------------------------------------------------
 * @see \Core\Crypt\Hash::password()
 * @method static false|string|null password(string $password, $use_algo = false)
 * ----------------------------------------------------------------------------------
 * @see \Core\Crypt\Hash::passwordCheck()
 * @method static string|false passwordCheck(string $password, string $hashedPassword, $use_algo = false)
 * ----------------------------------------------------------------------------------
 * @see \Core\Crypt\Hash::passwordRehash()
 * @method static string passwordRehash(string $password, string $hashedPassword)
 * ----------------------------------------------------------------------------------
 * @mixin \Core\Crypt\Hash
 * @see \Core\Crypt\Hash
 */

class Hash extends Facade {

    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return Hash::class;
    }
}
