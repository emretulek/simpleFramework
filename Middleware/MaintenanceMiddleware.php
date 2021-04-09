<?php 



namespace Middleware;


use Auth;
use Router;

class MaintenanceMiddleware {

    function before()
    {
        if(config('settings.system_maintenance')){
            if(Auth::guard('admin')){
                return;
            }
            if(request()->matchUri('/admin.*')){
                return;
            }
            if(!Router::matchName('maintenance')){
                redirect('/maintenance');
            }
        }
    }
}
