<?php


namespace Core\Model;


use Exception;
use Throwable;

class ModelException extends Exception
{
    public function __construct($message = "", $code = E_WARNING, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
