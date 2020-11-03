<?php 
/**
 * @Created 03.11.2020 22:13:36
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class IMiddlewareAfter
 * @package Core\Middleware
 */


namespace Core\Middleware;


Interface IMiddlewareAfter {
    function after($response);
}
