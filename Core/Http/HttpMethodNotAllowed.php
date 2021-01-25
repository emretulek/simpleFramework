<?php

namespace Core\Http;

use Exception;
use Throwable;

class HttpMethodNotAllowed extends Exception
{
    public function __construct($message = "Http methode not allowed.", $code = E_NOTICE, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
