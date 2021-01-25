<?php

namespace Middleware;

use Core\Http\HttpNotFound;
use Core\Middleware\IMiddlewareAfter;

class AfterMiddleware implements IMiddlewareAfter
{
    function after($response)
    {
        if (empty($response)) {
            throw new HttpNotFound("Empty Response after middleware (test)");
        }

        return $response;
    }
}
