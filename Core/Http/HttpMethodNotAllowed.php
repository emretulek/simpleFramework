<?php 
/**
 * @Created 18.05.2020 02:04:17
 * @Project simpleFramework
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class HttpMethodNotAllowed
 * @package Core\Http
 */


namespace Core\Http;


use Exception;
use Throwable;

Class HttpMethodNotAllowed extends Exception{

    public function __construct($message = "Http methode not allowed.", $code = E_NOTICE, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
