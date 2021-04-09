<?php
namespace Middleware;

use Core\Http\HttpMethodNotAllowed;
use Request;

Class AjaxCheck {

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
