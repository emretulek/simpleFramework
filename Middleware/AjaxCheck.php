<?php
namespace Middleware;

use Core\Http\HttpNotFound;
use Core\Middleware\IMiddleware;
use Request;

Class AjaxCheck Implements IMiddleware {

    /**
     * @throws HttpNotFound
     */
    public function before()
    {
        if(Request::isAjax() == false){
            throw new HttpNotFound();
        }
    }

    public function after()
    {
        // TODO: Implement after() method.
    }
}
