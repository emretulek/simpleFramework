<?php


namespace Core\Facades;

/**
 * Class Session
 * @package Core\Facades
 * @mixin \Core\Cookie\Cookie
 */

class Cookie extends Facades
{
    static function getOriginalClassName()
    {
        return \Core\Cookie\Cookie::class;
    }
}
