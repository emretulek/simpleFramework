<?php 



namespace Middleware;


use Auth;
use Core\Middleware\IMiddlewareBefore;
use Router;

class MaintenanceMiddleware implements IMiddlewareBefore {

    function before()
    {
        if($maintanence = config('settings.system_maintenance')){
            if(Auth::guard('admin')){
                return;
            }
            if(request()->matchUri('/admin.*')){
                return;
            }
            if(!Router::matchName('maintenance')){
                redirect('/maintenance');
            }
            return;
        }
    }
}
