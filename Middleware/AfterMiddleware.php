<?php 

namespace Middleware;

use Core\Http\Response;

class AfterMiddleware {

    function after(Response $response)
    {
        return $response->headers([
            'X-Content-Type-Options' => "nosniff",
            'X-XSS-Protection' => "1; mode=block"
        ]);
    }
}
