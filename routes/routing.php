<?php

use Core\Middleware\CsrfCheck;

Router::group(['middleware'=>CsrfCheck::class], function (){
    Router::any('/a', function (){
        dump($_SESSION);
    });
    Router::any('/test', function (){
        dump("test");
    });
});

Router::autoRute();
