<?php 
/**
 * @Created 03.11.2020 22:36:55
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class AfterMiddleware
 * @package Middleware
 */


namespace Middleware;


use Core\Http\HttpNotFound;
use Core\Middleware\IMiddlewareAfter;

class AfterMiddleware implements IMiddlewareAfter {


    function after($response)
    {
        if(empty($response)){
            throw new HttpNotFound("Empty Response after middleware (test)");
        }

        return $response;
    }
}
