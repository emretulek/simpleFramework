<?php 
/**
 * @Created 15.12.2020 00:35:38
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class Hook
 * @package Core\Facades
 */


namespace Core\Facades;


class Hook extends Facade {

    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \Core\Hook\Hook::class;
    }
}
