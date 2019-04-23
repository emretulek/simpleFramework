<?php


namespace Core\Facades;

/**
 * Class Session
 * @package Core\Facades
 * @mixin \Core\Session\Session
 */

class Session extends Facades
{
    static function getOriginalClassName()
    {
        return \Core\Session\Session::class;
    }
}
