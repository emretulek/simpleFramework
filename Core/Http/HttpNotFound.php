<?php
namespace Core\Http;

use Exception;
use Throwable;

Class HttpNotFound extends Exception{

    public function __construct($message = "", $code = E_NOTICE, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
