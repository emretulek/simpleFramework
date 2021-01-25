<?php

namespace Core\Middleware;

interface IMiddlewareAfter
{
    function after($response);
}
