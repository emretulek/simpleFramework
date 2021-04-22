<?php

Router::any('/', "home@index");

Router::any('session1', function () {
    Session::set('test', 10);
    Session::set('test2', 15);
    sleep(2);

    dump($_SESSION);
    echo 'session1';
});

Router::any('session2', function () {
    Session::set('testç', 1);
    Session::set('test3', 1);
    dump($_SESSION);
    sleep(4);
    echo 'session2';
});
Router::any('session3', function () {

    Session::set('registered', 1);
    dump($_SESSION);
    //sleep(3);
    echo 'session3';
});

//session_destroy();
