<?php

namespace Core\Controller;


use Core\App;
use Core\View\View;


class Controller
{
    /**
     * @return App
     */
    final protected function app(): App
    {
        return App::getInstance();
    }

    /**
     * @return View
     */
    final protected function view():View
    {
         return $this->app()->resolve(View::class);
    }
}

