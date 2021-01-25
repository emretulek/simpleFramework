<?php

namespace Middleware;

use Core\Http\HttpMethodNotAllowed;
use Core\Middleware\IMiddlewareBefore;
use Request;

class AjaxCheck implements IMiddlewareBefore
{
    /**
     * @throws HttpMethodNotAllowed
     */
    public function before()
    {
        if (Request::isAjax() == false) {
            throw new HttpMethodNotAllowed();
        }
    }
}
