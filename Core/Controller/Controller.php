<?php

namespace Core\Controller;


use Core\App;
use Core\Config\Config;
use Core\View\View;


class Controller
{

    public function main()
    {

    }

    /**
     * @return View;
     */
    protected function view()
    {
         return App::getInstance(View::class);
    }

    /**
     * @param string $class
     * @return mixed
     */
    protected function model(string $class)
    {
        return App::getInstance(str_replace('/', '\\',Config::get('path.model').'/'.$class));
    }
}

