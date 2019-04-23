<?php

namespace Core\Facades;

/**
 * Class Session
 * @package Core\Facades
 * @mixin \Core\Config\Config
 */
class Config extends Facades
{
    static function getOriginalClassName()
    {
        return \Core\Config\Config::class;
    }
}
