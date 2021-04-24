<?php


namespace Core\Http\HttpException;


use Throwable;

class ForbiddenHttpException extends HttpException
{
    public function __construct($message = 'Forbidden', array $headers = [], $code = E_NOTICE, Throwable $previous = null)
    {
        parent::__construct($message, 403, $headers, $code, $previous);
    }
}
