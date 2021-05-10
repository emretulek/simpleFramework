<?php


namespace Core\Http\HttpException;


use Throwable;

class InternalServerErrorHttpException extends HttpException
{
    public function __construct($message = 'Internal Server Error', array $headers = [], $code = E_ERROR, Throwable $previous = null)
    {
        parent::__construct($message, 500, $headers, $code, $previous);
    }
}
