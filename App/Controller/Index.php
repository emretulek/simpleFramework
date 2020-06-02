<?php

namespace App\Controller;

use Core\Controller;


class Index extends Controller
{
    public function main()
    {
        $this->view()->template('index', ['hello' => 'Hello World.'])->render();
        //$this->view()->page('index', ['hello' => 'Hello World.'])->render();

        //template('index', ['hello' => 'Hello World.']);
        //page('index', ['hello' => 'Hello World.']);
    }
}

