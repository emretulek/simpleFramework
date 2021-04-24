<?php
namespace Middleware;

use Core\Http\HttpException\MethodNotAllowedHttpException;
use Request;

Class AjaxCheck {

    /**
     * @throws MethodNotAllowedHttpException
     */
    public function before()
    {
        if(Request::isAjax() == false){
            throw new MethodNotAllowedHttpException();
        }
    }
}
