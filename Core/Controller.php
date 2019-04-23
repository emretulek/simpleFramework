<?php

namespace Core;


class Controller
{
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
        return App::getInstance('App\\Model\\'.$class);
    }
}

