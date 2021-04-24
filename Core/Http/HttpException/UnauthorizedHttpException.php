<?php


namespace Core\Http\HttpException;


use Throwable;

class UnauthorizedHttpException extends HttpException
{
    public function __construct(int $statusCode = 401, $message = 'Unauthorized', array $headers = [], $code = E_NOTICE, Throwable $previous = null)
    {
        parent::__construct($statusCode, $message, $headers, $code, $previous);
    }
}
