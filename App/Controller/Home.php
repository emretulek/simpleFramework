<?php

namespace App\Controller;

use Core\Controller\Controller;


class Home extends Controller
{
    public function index()
    {
        /*return partial('page/index', [
            'content' => 'This content here'
        ]);*/

        /*return page('index', [
            'content' => 'This content here'
        ]);*/

        return layout('index', [
            'content' => 'This content here'
        ]);
    }
}

