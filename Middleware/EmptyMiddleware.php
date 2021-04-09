<?php


namespace Middleware;


use Core\Middleware\MiddlewareInterface;

class EmptyMiddleware implements MiddlewareInterface
{
    public function before()
    {

    }

    public function after($response)
    {

    }
}
