<?php

namespace App\Controller;

use Core\Controller\Controller;


class Home extends Controller
{
    public function index()
    {
        //$this->view()->layout('index', ['hello' => 'Hello World.'])->render();
        //return layout('index', ['hello' => 'Hello World.']);

        //$this->view()->page('index', ['hello' => 'Hello World.'])->render();
        //page('index', ['hello' => 'Not found.'])->render(404);

        return layout('index', ['content' => 'This content here']);
    }
}

