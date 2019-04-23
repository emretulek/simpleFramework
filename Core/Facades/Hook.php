<?php
namespace Core\Facades;

/**
 * Class Hook
 * @package Core\Facades
 * @mixin \Core\Hook\Hook
 */
Class Hook extends Facades{

    public static function getOriginalClassName()
    {
        return \Core\Hook\Hook::class;
    }
}
