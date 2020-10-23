<?php
namespace Middleware;

use Core\Http\HttpMethodNotAllowed;
use Core\Middleware\IMiddleware;
use Request;

Class AjaxCheck Implements IMiddleware {

    /**
     * @throws HttpMethodNotAllowed
     */
    public function before()
    {
        if(Request::isAjax() == false){
            throw new HttpMethodNotAllowed();
        }
    }
}
