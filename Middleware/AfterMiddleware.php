<?php 

namespace Middleware;


use Core\Http\HttpNotFound;
use Core\Middleware\IMiddlewareAfter;

class AfterMiddleware implements IMiddlewareAfter {


    function after($response)
    {
        return response((string) $response, http_response_code(), [
            'X-Content-Type-Options' => "nosniff",
            'X-XSS-Protection' => "1; mode=block"
        ]);
    }
}
