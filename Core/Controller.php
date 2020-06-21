<?php

namespace Core;


use Core\Config\Config;


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

