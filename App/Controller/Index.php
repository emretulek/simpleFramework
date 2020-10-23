<?php

namespace App\Controller;

use Core\Controller\Controller;


class Index extends Controller
{
    public function main()
    {
        //$this->view()->template('index', ['hello' => 'Hello World.'])->render();
        //return template('index', ['hello' => 'Hello World.']);

        //$this->view()->page('index', ['hello' => 'Hello World.'])->render();
        //page('index', ['hello' => 'Not found.'])->render(404);
        return page('index', ['hello' => 'Hello World.']);
    }
}

