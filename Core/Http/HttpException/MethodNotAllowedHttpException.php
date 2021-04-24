<?php

namespace Core\Http\HttpException;

use Throwable;

class MethodNotAllowedHttpException extends HttpException
{
    public function __construct($message = 'Method Not Allowed', array $headers = [], $code = E_NOTICE, Throwable $previous = null)
    {
        parent::__construct($message, 405, $headers, $code, $previous);
    }
}
