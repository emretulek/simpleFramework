<?php

namespace Core\Facades;

/**
 * @see \Core\Csrf\Csrf::generateToken()
 * @method static string generateToken()
 * ----------------------------------------------
 * @see \Core\Csrf\Csrf::token()
 * @method static string token()
 * ---------------------------------------------
 * @see \Core\Csrf\Csrf::refreshToken()
 * @method static string refreshToken()
 * ---------------------------------------------
 * @see \Core\Csrf\Csrf::old_token()
 * @method static string old_token()
 * ---------------------------------------------
 * @see \Core\Csrf\Csrf::check()
 * @method static bool check()
 * ---------------------------------------------
 * @see \Core\Csrf\Csrf::checkPost()
 * @method static bool checkPost()
 * ---------------------------------------------
 * @see \Core\Csrf\Csrf::checkGet()
 * @method static bool checkGet()
 * ---------------------------------------------
 * @see \Core\Csrf\Csrf::checkCookie()
 * @method static bool checkCookie()
 * ---------------------------------------------
 * @mixin \Core\Csrf\Csrf
 * @see \Core\Csrf\Csrf
 */
class Csrf extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \Core\Csrf\Csrf::class;
    }
}
