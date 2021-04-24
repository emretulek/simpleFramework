<?php

namespace Core\Http\HttpException;

use Exception;
use Throwable;

class BadRequestHttpException extends HttpException
{
    public function __construct($message = 'Bad Request', array $headers = [], $code = E_NOTICE, Throwable $previous = null)
    {
        parent::__construct($message, 400, $headers, $code, $previous);
    }
}
