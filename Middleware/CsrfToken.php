<?php 
/**
 * @Created 22.10.2020 15:50:42
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class Csrf
 * @package Middleware
 */


namespace Middleware;


use Core\Csrf\Csrf;
use Core\Middleware\IMiddleware;

class CsrfToken implements IMiddleware {

    function before()
    {
        Csrf::generateToken();
    }
}
