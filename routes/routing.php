<?php

use Middleware\AfterMiddleware;


Router::any('/', "index@main");
Router::get('/test', "index@main");
Router::get('/empty-response', function (){
    return null;
})::middleware(AfterMiddleware::class);

//Router::autoRute();
