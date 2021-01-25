<?php

namespace Core\Http;

use Exception;
use Throwable;

class HttpNotFound extends Exception
{
    public function __construct($message = "Not Found", $code = E_NOTICE, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
