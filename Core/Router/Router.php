<?php

namespace Core\Router;

use Core\App;
use Core\Http\HttpNotFound;
use Core\Http\Request;
use Core\Log\LogException;
use Core\Config\Config;
use ArgumentCountError;
use Closure;
use Core\View;
use Exception;
use ReflectionException;
use ReflectionMethod;

/**
 * @class Router
 *
 * Gelen http isteklerini düzenler ve yönlendirir.
 */
class Router
{

    private static $routeID = 0;
    private static $routes = [];

    private static $groupID = 0;
    private static $groups = [
        'prefix' => [],
        'namespace' => [],
        'middleware' => []
    ];

    private static $prefix = '';
    private static $nameSpace = '';

    private static $controller = 'index';
    private static $method = 'main';
    private static $params = [];

    private static $errors = [];

    private static $matchPatterns = [
        '#/\*#' => '/(.*)',
        '#\{id\}#' => '([0-9]+)',
        '#\{id\?}#' => '?([0-9]+)?',
        '#\{int\}#' => '([0-9\-]+)',
        '#\{int\?}#' => '?([0-9\-]+)?',
        '#\{string\}#' => '([\w\._-]+)',
        '#\{string\?}#' => '?([\w\._-]+)?',
        '#\{.*\}#' => '([\w\._-]+)',
        '#\{.*\?}#' => '?([\w\._-]+)?',
        '#/main#' => '/',
        '#/index#' => '/'
    ];

    /**
     * @param callable $callback
     */
    public static function group(callable $callback)
    {
        self::$groupID++;

        call_user_func($callback);

        if (self::$groupID > 0) {
            unset(self::$groups['prefix'][self::$groupID]);
            unset(self::$groups['namespace'][self::$groupID]);
            unset(self::$groups['middleware'][self::$groupID]);
        }

        self::$groupID--;
    }


    /**
     * @param string $prefix
     * @return Router
     */
    public static function prefix(string $prefix)
    {
        self::$groups['prefix'][self::$groupID+1] = trim($prefix, '/');

        return App::getInstance(self::class);
    }


    /**
     * @param string $routeName
     * @return Router
     */
    public static function name(string $routeName)
    {
        self::$routes[self::$routeID]['name'] = $routeName;

        return App::getInstance(self::class);
    }


    /**
     * @param string $nameSpace
     * @return Router
     */
    public static function nameSpace(string $nameSpace)
    {
        self::$groups['namespace'][self::$groupID+1] = $nameSpace;

        return App::getInstance(self::class);
    }

    /**
     * @ingnore test
     */
    public static function middleware($middlewares)
    {
        if (is_callable($middlewares)) {
            self::$groups['middleware'][self::$groupID+1] = $middlewares;
        } else {
            self::$groups['middleware'][self::$groupID+1] = ['Middleware\\' . $middlewares, 'handle'];
        }

        return App::getInstance(self::class);
    }

    /**
     * Yeni bir yönlendirme ekler.
     *
     * @param string $pattern yönlendirilecek istek deseni (regex)
     * @param string|callback $cmp İsteğin yönlendirileceği callback veya controller TODO controller@method
     * @param null $method zorlanacak istek türü POST, GET
     * @return self
     */
    private static function addRoute($pattern, $cmp, $method)
    {
        self::$routeID++;

        self::$routes[self::$routeID]['pattern'] = $pattern;
        self::$routes[self::$routeID]['cmp'] = $cmp;
        self::$routes[self::$routeID]['method'] = $method;
        self::$routes[self::$routeID]['name'] = null;
        self::$routes[self::$routeID]['prefix'] = implode('/', self::$groups['prefix']);
        self::$routes[self::$routeID]['namespace'] = implode('\\', self::$groups['namespace']);
        self::$routes[self::$routeID]['middleware'] = self::$groups['middleware'];

        return App::getInstance(self::class);
    }


    public static function autoRute()
    {
        $segments = Request::segments();
        $segments = array_slice($segments, count(self::$groups['prefix']));
        $controller = isset($segments[0]) ? array_shift($segments) : self::$controller;
        $method = isset($segments[0]) ? array_shift($segments) : self::$method;
        $params = $segments ? implode('/', array_map(function ($item) {
            return '(' . $item . ')';
        }, $segments)) : '';

        return self::addRoute('/' . $controller . '/' . $method . '/' . $params, $controller . '@' . $method, null);
    }

    /**
     * @method Router::addRoute GET, POST tüm istek türlerini kabul eder
     *
     * @param string $pattern Yönlendirilecek istek deseni (regex)
     * @param string|callback $cmp İsteğin yönlendirileceği callback veya controller TODO controller@method
     * @return self
     */
    public static function any($pattern, $cmp)
    {
        return self::addRoute($pattern, $cmp, NULL);
    }

    /**
     * @method Router::addRoute GET tüm istek türlerini kabul eder
     *
     * @param string $pattern Yönlendirilecek istek deseni (regex)
     * @param string|callback $cmp İsteğin yönlendirileceği callback veya controller TODO controller@method
     * @return self
     */
    public static function get($pattern, $cmp)
    {
        return self::addRoute($pattern, $cmp, 'GET');
    }

    /**
     * @method Router::addRoute POST tüm istek türlerini kabul eder
     *
     * @param string $pattern Yönlendirilecek istek deseni (regex)
     * @param string|callback $cmp İsteğin yönlendirileceği callback veya controller TODO controller@method
     * @return self
     */
    public static function post($pattern, $cmp)
    {
        return self::addRoute($pattern, $cmp, 'POST');
    }

    /**
     * @method Router::addRoute PUT tüm istek türlerini kabul eder
     *
     * @param string $pattern Yönlendirilecek istek deseni (regex)
     * @param string|callback $cmp İsteğin yönlendirileceği callback veya controller TODO controller@method
     * @return self
     */
    public static function put($pattern, $cmp)
    {
        return self::addRoute($pattern, $cmp, 'PUT');
    }

    /**
     * @method Router::addRoute DELETE tüm istek türlerini kabul eder
     *
     * @param string $pattern Yönlendirilecek istek deseni (regex)
     * @param string|callback $cmp İsteğin yönlendirileceği callback veya controller TODO controller@method
     * @return self
     */
    public static function delete($pattern, $cmp)
    {
        return self::addRoute($pattern, $cmp, 'DELETE');
    }

    /**
     * @method Router::addRoute CONNECT tüm istek türlerini kabul eder
     *
     * @param string $pattern Yönlendirilecek istek deseni (regex)
     * @param string|callback $cmp İsteğin yönlendirileceği callback veya controller TODO controller@method
     * @return self
     */
    public static function connect($pattern, $cmp)
    {
        return self::addRoute($pattern, $cmp, 'CONNECT');
    }

    /**
     * @method Router::addRoute HTTP tüm istek türlerini kabul eder
     *
     * @param string $pattern Yönlendirilecek istek deseni (regex)
     * @param string|callback $cmp İsteğin yönlendirileceği callback veya controller TODO controller@method
     * @return self
     */
    public static function http($pattern, $cmp)
    {
        return self::addRoute($pattern, $cmp, 'HTTP');
    }

    /**
     * @method Router::addRoute OPTIONS tüm istek türlerini kabul eder
     *
     * @param string $pattern Yönlendirilecek istek deseni (regex)
     * @param string|callback $cmp İsteğin yönlendirileceği callback veya controller TODO controller@method
     * @return self
     */
    public static function options($pattern, $cmp)
    {
        return self::addRoute($pattern, $cmp, 'OPTIONS');
    }


    /**
     * Eklenen yönlendirmeleri ilgili controller ve methoda iletir.
     *
     * @param $controller
     * @param $method
     * @param $params
     * @return mixed
     * @throws LogException
     */
    private static function route($controller, $method, $params)
    {
        try {
            //class mevcut değilse
            if (!class_exists($controller)) {
                throw new HttpNotFound($controller . ' : class not found.');
                //method mevcut değilse
            } elseif (!method_exists($controller, $method)) {
                throw new HttpNotFound($method . ' : method not found in ' . $controller);
            } else {
                //method public değilse
                $reflection = new ReflectionMethod($controller, $method);
                if (!$reflection->isPublic()) {
                    throw new HttpNotFound($method . ' : method is not a public');
                }
            }
            return call_user_func_array([new $controller, $method], $params);

        } catch (ArgumentCountError $exception) {
            throw new LogException($exception->getMessage(), LogException::TYPE['NOTICE'], $exception);
        } catch (ReflectionException $exception) {
            throw new LogException($exception->getMessage(), LogException::TYPE['NOTICE'], $exception);
        }catch (HttpNotFound $exception){
            throw new LogException($exception->getMessage(), LogException::TYPE['NOTICE'], $exception);
        }catch (Exception $exception){
            throw new LogException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * Eklenen yönlendirmeleri çalıştırır. İlk eşleşmede durur.
     *
     * @return bool|null
     */
    public static function start()
    {
        try {

            foreach (self::$routes as $name => $route) {
                //prefix
                self::setPrefix($route['prefix']);

                $route['pattern'] = self::$prefix . $route['pattern'];

                if (false !== ($matches = self::rootMatch($route['pattern'], Request::path()))) {


                    //Request method check
                    if (Request::method($route['method']) == false) {
                        return self::errors(405);
                    }

                    //load middlewares
                    foreach ($route['middleware'] as $middleware) {
                        App::caller($middleware, null);
                    }

                    //Router callback clouser
                    if (is_callable($route['cmp'])) {
                        try {
                            return call_user_func_array($route['cmp'], $matches);
                        } catch (ArgumentCountError $exception) {
                            throw new LogException($exception->getMessage(), LogException::TYPE['NOTICE'], $exception);
                        }
                    }

                    $cmp = explode('@', $route['cmp']);
                    //namespace
                    self::setNameSpace($route['namespace']);
                    self::setController(array_shift($cmp));
                    self::setMethod(array_shift($cmp));
                    self::setParams($matches);

                    return self::route(self::$nameSpace . self::$controller, self::$method, self::$params);
                }
            }
        } catch (LogException $exception) {
            $exception->debug();
        }

        return self::errors(404);
    }


    /**
     * Yönlendirme kalıplarını regex olarak dönüştürür.
     *
     * @param string $pattern
     * @param $requestUri
     * @return array|bool
     */
    private static function rootMatch(string $pattern, $requestUri)
    {
        $pattern = preg_replace(array_keys(self::$matchPatterns), self::$matchPatterns, $pattern);
        $pattern = '/' . trim($pattern, '/');
        $requestUri = '/' . trim($requestUri, '/');
        $segments = array_values(array_filter(explode("/", $requestUri)));

        if (end($segments) == 'main') {
            $segments = array_slice($segments, 0, -1);
            $requestUri = '/' . implode('/', $segments);
        }
        if (end($segments) == 'index') {
            $segments = array_slice($segments, 0, -1);
            $requestUri = '/' . implode('/', $segments);
        }

        if (preg_match('#^' . $pattern . '$#', $requestUri, $matches)) {
            return count($matches) > 1 ? array_slice($matches, 1) : array();
        }

        return false;
    }


    /**
     * @param $prefix
     */
    private static function setPrefix($prefix)
    {
        self::$prefix = $prefix ? trim($prefix,'/') : null;
    }

    /**
     * @param null $nameSpace
     */
    private static function setNameSpace($nameSpace = null)
    {
        $controllerPath = str_replace('/', '\\', Config::get('path.controller')) . '\\';
        $subPath = $nameSpace ? str_replace('/', '\\', $nameSpace) . '\\' : '';
        self::$nameSpace = $controllerPath . $subPath;
    }

    /**
     * Girilen class kullanılabilir hale getirir
     * @param $className
     */
    private static function setController($className)
    {
        $className = preg_replace('#[^a-z0-9_/]#i', '', $className);
        self::$controller = $className ? str_replace('/', '\\', $className) : self::$controller;
    }

    /**
     * Girilen method kullanılabilir hale getirir
     *
     * @param $methodName
     */
    private static function setMethod($methodName)
    {
        $methodName = preg_replace('#[^a-z0-9_]#i', '', $methodName);
        self::$method = $methodName ? $methodName : self::$method;
    }

    /**
     * Girilen parametreleri kullanılabilir hale getirir
     *
     * @param $params
     */
    private static function setParams($params)
    {
        if (is_array($params)) {
            self::$params = $params;
        } elseif (empty($params)) {
            self::$params = [];
        } else {
            self::$params = [$params];
        }
    }

    /**
     * Aktif namespace döndürür
     *
     * @return string
     */
    public static function getNameSpace()
    {
        return self::$nameSpace;
    }

    /**
     * Aktif controller döndürür
     *
     * @return string
     */
    public static function getController()
    {
        return self::$controller;
    }

    /**
     * Aktif Method döndürür
     *
     * @return string
     */
    public static function getMethod()
    {
        return self::$method;
    }

    /**
     * Aktif parametreleri döndürür
     *
     * @return array
     */
    public static function getParams()
    {
        return self::$params;
    }

    /**
     * Aktif dizini döndürür /admin için admin gibi.
     *
     * @return string
     */
    public static function getPrefix()
    {
        return trim(self::$prefix, '/');
    }

    /**
     * Router custom errors
     *
     * @param $http_code
     * @param callable|null $callback
     * @return mixed
     */
    public static function errors($http_code, callable $callback = null)
    {
        $view = new View();

        if ($callback instanceof Closure) {
            self::$errors[$http_code] = $callback;
        }

        if (empty(self::$errors[404])) {
            self::$errors[404] = function () use ($view){
                $view->path('errors/404.html', null, null)->render(404);
            };
        }
        if (empty(self::$errors[405])) {
            self::$errors[405] = function () use ($view){
                $view->path('errors/405.html', null, null)->render(405);
            };
        }

        return call_user_func(self::$errors[$http_code]);
    }
}
