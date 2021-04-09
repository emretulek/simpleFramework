<?php

namespace Core\Middleware;

interface MiddlewareInterface
{
    public function before();
    public function after($response);
}
