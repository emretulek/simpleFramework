<?php


namespace Core\Http\HttpException;


use RuntimeException;
use Throwable;

class HttpException extends RuntimeException
{
    private int $statusCode;
    private array $headers;

    public function __construct(string $message = '', int $statusCode = 0, array $headers = [], $code = E_NOTICE, Throwable $previous = null)
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;

        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode():int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }
}
