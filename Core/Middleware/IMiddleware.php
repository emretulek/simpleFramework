<?php


namespace Core\Middleware;


Interface IMiddleware
{
    function before();
    function after();
}
