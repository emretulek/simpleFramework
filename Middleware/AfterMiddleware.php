<?php 

namespace Middleware;


class AfterMiddleware {

    function after($response)
    {
        return response((string) $response, http_response_code(), [
            'X-Content-Type-Options' => "nosniff",
            'X-XSS-Protection' => "1; mode=block"
        ]);
    }
}
