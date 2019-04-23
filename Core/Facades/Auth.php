<?php

namespace Core\Facades;

/**
 * Class Session
 * @package Core\Facades
 *
 * @mixin \Core\Auth\Auth
 */

class Auth extends Facades
{
    static function getOriginalClassName()
    {
        return \Core\Auth\Auth::class;
    }
}
