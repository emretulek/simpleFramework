<?php

namespace App\Controller;

use Core\Controller;

class Index extends Controller
{
    public function main()
    {
        $this->view()->page('index', ['hello' => 'Hello World.'])->render();
    }
}

