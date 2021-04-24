<?php

namespace Core\Http\HttpException;

use Throwable;

class NotFoundHttpException extends HttpException
{
    public function __construct($message = 'Not Found', array $headers = [], $code = E_NOTICE, Throwable $previous = null)
    {
        parent::__construct($message, 404, $headers, $code, $previous);
    }
}
