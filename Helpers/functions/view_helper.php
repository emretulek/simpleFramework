<?php
use Core\View;

if (!function_exists('page')) {
    /**
     * Core\View::page metodunun eş değeri
     *
     * @param $fileName
     * @param array $data
     * @return View
     */
    function page($fileName, $data = array())
    {
        $view = new View();
        return $view->page($fileName, $data)->render();
    }
}

if (!function_exists('viewPath')) {
    /**
     * Core\View::part metodunun eş değeri
     *
     * @param $fileName
     * @param array $data
     * @return View
     */
    function viewPath($fileName, $data = array())
    {
        $view = new View();
        return $view->path($fileName, $data)->render();
    }
}

if (!function_exists('json')) {
    /**
     * Core\View::json metodunun eş değeri
     *
     * @param array $data
     * @return View
     */
    function json($data)
    {
        $view = new View();
        return $view->json($data)->render();
    }
}


if (!function_exists('jsonSuccess')) {
    /**
     * Json verisini success olarak render eder
     *
     * @param null $message
     * @param null $location
     * @param null $data
     * @return View
     */
    function jsonSuccess($message = null, $location = null, $data = null)
    {
        $view = new View();
        return $view->json(['type' => 'success', 'message' => $message, 'location' => $location, 'data' => $data])->render();
    }
}

if (!function_exists('jsonError')) {
    /**
     * Json verisini error olarak render eder
     *
     * @param null $message
     * @param null $location
     * @param null $data
     * @return View
     */
    function jsonError($message = null, $location = null, $data = null)
    {
        $view = new View();
        return $view->json(['type' => 'error', 'message' => $message, 'location' => $location, 'data' => $data])->render();
    }
}

if (!function_exists('template')) {
    /**
     * Core\View::template metodunun eş değeri
     *
     * @param $fileName
     * @param array $data
     * @return View
     */
    function template($fileName, $data = array())
    {
        $view = new View();
        return $view->template($fileName, $data)->render();
    }
}

if (!function_exists('setTemplate')) {
    /**
     * Core\View::setTemplate metodunun eş değeri
     *
     * @param $template
     * @return View
     */
    function setTemplate($template)
    {
        $view = new View();
        return $view->setTemplate($template);
    }
}
