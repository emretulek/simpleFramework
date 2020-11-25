<?php

namespace Core\Router;

use Core\App;
use Core\Exceptions\Exceptions;
use Core\Http\HttpMethodNotAllowed;
use Core\Http\HttpNotFound;
use Core\Http\Request;
use Core\Config\Config;
use ArgumentCountError;
use Closure;
use Core\Http\Response;
use Core\View\View;
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
    private static int $routeID = 0;
    private static array $routes = [];
    private static array $currentRoute = [];

    private static array $methodPrefix = [];
    private static array $methodNamespace = [];
    private static array $methodMidleware = [];

    private static string $prefix = '';
    private static string $nameSpace = '';

    private static string $controller = 'index';
    private static string $method = 'main';
    private static array $params = [];

    private static array $errors = [];

    private static array $matchPatterns = [
        '#/\*#' => '/(.*)',
        '#\{id\}#' => '([0-9]+)',
        '#\{id\?}#' => '?([0-9]+)?',
        '#\{int\}#' => '([0-9\-]+)',
        '#\{int\?}#' => '?([0-9\-]+)?',
        '#\{string\}#' => '([\s\w\._-]+)',
        '#\{string\?}#' => '?([\s\w\._-]+)?',
        '#\{\*\}#' => '(.+)',
        '#\{\*\?}#' => '?(.*)?'
    ];


    /**
     * @param array $options
     */
    public static function global(array $options)
    {
        if (array_key_exists('middleware', $options)) {
            self::$methodMidleware[] = $options['middleware'];
        }

        if (array_key_exists('namespace', $options)) {
            self::$methodNamespace[] = $options['namespace'];
        }

        if (array_key_exists('prefix', $options)) {
            self::$methodPrefix[] = $options['prefix'];
        }
    }


    /**
     * @param array $options
     * @param callable $callback
     */
    public static function group(array $options, callable $callback)
    {
        if (array_key_exists('middleware', $options)) {
            self::$methodMidleware[] = $options['middleware'];
        }

        if (array_key_exists('namespace', $options)) {
            self::$methodNamespace[] = $options['namespace'];
        }

        if (array_key_exists('prefix', $options)) {
            self::$methodPrefix[] = $options['prefix'];
        }

        call_user_func($callback);

        if (array_key_exists('middleware', $options)) {
            array_pop(self::$methodMidleware);
        }

        if (array_key_exists('namespace', $options)) {
            array_pop(self::$methodNamespace);
        }

        if (array_key_exists('prefix', $options)) {
            array_pop(self::$methodPrefix);
        }
    }


    /**
     * @param string $prefix
     * @return Router
     */
    public static function prefix(string $prefix)
    {
        if (!empty(self::$routes[self::$routeID]['prefix'])) {
            self::$routes[self::$routeID]['prefix'] .= '/' . trim($prefix, '/');
        } else {
            self::$routes[self::$routeID]['prefix'] = trim($prefix, '/');
        }

        return new static();
    }


    /**
     * @param string $routeName
     * @return Router
     */
    public static function name(string $routeName)
    {
        self::$routes[self::$routeID]['name'] = $routeName;

        return new static();
    }


    /**
     * @param string $nameSpace
     * @return Router
     */
    public static function nameSpace(string $nameSpace)
    {
        if (isset(self::$routes[self::$routeID]['namespace']) && !empty(self::$routes[self::$routeID]['namespace'])) {

            self::$routes[self::$routeID]['namespace'] .= '\\' . trim($nameSpace, '\\');
        } else {
            self::$routes[self::$routeID]['namespace'] = trim($nameSpace, '\\');
        }

        return new static();
    }

    /**
     * @param $middleware
     * @return Router
     */
    public static function middleware($middleware)
    {
        if (is_array($middleware)) {
            self::$routes[self::$routeID]['middleware'] = array_merge(self::$routes[self::$routeID]['middleware'], $middleware);
        } else {
            array_push(self::$routes[self::$routeID]['middleware'], $middleware);
        }

        return new static();
    }

    /**
     * Yeni bir yönlendirme ekler.
     *
     * @param string $pattern yönlendirilecek istek deseni (regex)
     * @param string|callback $cmp İsteğin yönlendirileceği callback veya controller TODO controller@method
     * @param null $method zorlanacak istek türü POST, GET
     * @return self
     */
    private static function addRoute(string $pattern, $cmp, $method)
    {
        self::$routeID++;

        self::$routes[self::$routeID]['requestUri'] = Request::requestUri();
        self::$routes[self::$routeID]['pattern'] = $pattern;
        self::$routes[self::$routeID]['cmp'] = $cmp;
        self::$routes[self::$routeID]['method'] = $method;
        self::$routes[self::$routeID]['name'] = null;

        self::$routes[self::$routeID]['prefix'] = implode('/', self::$methodPrefix);
        self::$routes[self::$routeID]['namespace'] = implode('\\', self::$methodNamespace);
        self::$routes[self::$routeID]['middleware'] = [];

        foreach (self::$methodMidleware as $middleware) {
            if (is_array($middleware)) {
                self::$routes[self::$routeID]['middleware'] = array_merge(self::$routes[self::$routeID]['middleware'], $middleware);
            } else {
                array_push(self::$routes[self::$routeID]['middleware'], $middleware);
            }
        }

        return new static();
    }


    /**
     * otomatik route oluşturur
     *
     * @return Router
     */
    public static function autoRute()
    {
        $segments = Request::segments();
        $segments = array_slice($segments, count(self::$methodPrefix));
        $controller = isset($segments[0]) ? array_shift($segments) : null;
        $controller = substr($controller, 0, 2) == '__' ? null : $controller;
        $method = isset($segments[0]) ? array_shift($segments) : null;
        $params = $segments ? implode('/', array_map(fn($item) => '{*}', $segments)) : '';

        $pattern = '/' . $controller . '/' . $method . '/' . $params;
        $pattern = preg_replace("#[^\w\d/]#iu", "", $pattern);
        $pattern = preg_quote($pattern);

        return self::any($pattern . $params, $controller . '@' . $method);
    }


    /**
     * @method Router::addRoute belirtilen tüm istek türlerini kabul eder
     *
     * @param $pattern
     * @param $cmp
     * @param array|string[] $methods ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS'] biri veya birkaçı
     * @return Router
     */
    public static function useMethod($pattern, $cmp, array $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS'])
    {
        return self::addRoute($pattern, $cmp, $methods);
    }

    /**
     * @method Router::addRoute 'GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS' tüm istek türlerini kabul eder
     *
     * @param string $pattern Yönlendirilecek istek deseni (regex)
     * @param string|callback $cmp İsteğin yönlendirileceği callback veya controller TODO controller@method
     * @return self
     */
    public static function any(string $pattern, $cmp)
    {
        return self::addRoute($pattern, $cmp, ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS']);
    }

    /**
     * @method Router::addRoute GET okuma
     *
     * @param string $pattern Yönlendirilecek istek deseni (regex)
     * @param string|callback $cmp İsteğin yönlendirileceği callback veya controller TODO controller@method
     * @return self
     */
    public static function get(string $pattern, $cmp)
    {
        return self::addRoute($pattern, $cmp, ['GET']);
    }

    /**
     * @method Router::addRoute POST oluşturma
     *
     * @param string $pattern Yönlendirilecek istek deseni (regex)
     * @param string|callback $cmp İsteğin yönlendirileceği callback veya controller TODO controller@method
     * @return self
     */
    public static function post(string $pattern, $cmp)
    {
        return self::addRoute($pattern, $cmp, ['POST']);
    }

    /**
     * @method Router::addRoute PUT güncelleme
     *
     * @param string $pattern Yönlendirilecek istek deseni (regex)
     * @param string|callback $cmp İsteğin yönlendirileceği callback veya controller TODO controller@method
     * @return self
     */
    public static function put(string $pattern, $cmp)
    {
        return self::addRoute($pattern, $cmp, ['PUT']);
    }

    /**
     * @method Router::addRoute DELETE silme
     *
     * @param string $pattern Yönlendirilecek istek deseni (regex)
     * @param string|callback $cmp İsteğin yönlendirileceği callback veya controller TODO controller@method
     * @return self
     */
    public static function delete(string $pattern, $cmp)
    {
        return self::addRoute($pattern, $cmp, ['DELETE']);
    }

    /**
     * @method Router::addRoute PATCH kısmi güncelleme
     *
     * @param string $pattern Yönlendirilecek istek deseni (regex)
     * @param string|callback $cmp İsteğin yönlendirileceği callback veya controller TODO controller@method
     * @return self
     */
    public static function patch(string $pattern, $cmp)
    {
        return self::addRoute($pattern, $cmp, ['PATCH']);
    }

    /**
     * @method Router::addRoute HEAD üst bilgi
     *
     * @param string $pattern Yönlendirilecek istek deseni (regex)
     * @param string|callback $cmp İsteğin yönlendirileceği callback veya controller TODO controller@method
     * @return self
     */
    public static function head(string $pattern, $cmp)
    {
        return self::addRoute($pattern, $cmp, ['HEAD']);
    }

    /**
     * @method Router::addRoute OPTIONS seçenekler
     *
     * @param string $pattern Yönlendirilecek istek deseni (regex)
     * @param string|callback $cmp İsteğin yönlendirileceği callback veya controller TODO controller@method
     * @return self
     */
    public static function options(string $pattern, $cmp)
    {
        return self::addRoute($pattern, $cmp, ['OPTIONS']);
    }


    /**
     * @param $controller
     * @param $method
     * @param $params
     * @return mixed
     * @throws HttpNotFound
     */
    private static function route($controller, $method, $params)
    {
        try {
            //class mevcut değilse
            if (!class_exists($controller)) {
                throw new HttpNotFound($controller . ' : class not found.', E_NOTICE);
                //method mevcut değilse
            } elseif (!method_exists($controller, $method)) {
                throw new HttpNotFound($method . ' : method not found in ' . $controller, E_NOTICE);
            } else {
                //method public değilse
                $reflection = new ReflectionMethod($controller, $method);
                if (!$reflection->isPublic()) {
                    throw new HttpNotFound($method . ' : method is not a public', E_NOTICE);
                }
            }

            return call_user_func_array([new $controller, $method], $params);

        } catch (ArgumentCountError $e) {
            throw new HttpNotFound($e->getMessage(), E_NOTICE, $e);
        } catch (ReflectionException $e) {
            throw new HttpNotFound($e->getMessage(), E_NOTICE, $e);
        }
    }

    /**
     * Eklenen yönlendirmeleri çalıştırır. İlk eşleşmede durur.
     *
     * @return void
     */
    public static function start()
    {
        try {

            foreach (self::$routes as $name => $route) {

                if (false !== ($matches = self::rootMatch($route['prefix'] . $route['pattern'], $route['requestUri']))) {

                    //current router
                    self::$currentRoute = $route;

                    //prefix
                    self::$prefix = $route['prefix'];

                    //Request method check
                    if (in_array(Request::method(), $route['method']) == false) {
                        throw new HttpMethodNotAllowed("Http method allowed " . implode(",", $route['method']));
                    }

                    //load middlewares before
                    foreach ($route['middleware'] as $middleware) {
                        if(is_callable([$middleware, 'before'])){
                            call_user_func([new $middleware, 'before']);
                        }
                    }

                    //Router callback clouser
                    if (is_callable($route['cmp'])) {
                        $response = self::startCallback($route['cmp'], $matches);
                    } else {
                        $response = self::startController($route, $matches);
                    }

                    //load middleware after
                    foreach ($route['middleware'] as $middleware) {
                        if(is_callable([$middleware, 'after'])){
                            $response = call_user_func([new $middleware, 'after'], $response);
                        }
                    }


                    if ($response instanceof View || $response instanceof Response) {
                        echo $response;
                    }else{
                        echo new Response($response);
                    }

                    return;
                }
            }

            throw new HttpNotFound('No match router.', E_NOTICE);

        } catch (HttpNotFound $e) {
            Exceptions::debug($e);
            return self::errors(404);
        } catch (HttpMethodNotAllowed $e) {
            Exceptions::debug($e);
            return self::errors(405);
        } catch (Exception $e) {
            Exceptions::debug($e);
            return self::errors(500);
        }
    }


    /**
     * @param callable $callback
     * @param $args
     * @return Response|View|null
     * @throws HttpNotFound
     */
    private static function startCallback(callable $callback, $args)
    {
        try {
            return call_user_func_array($callback, $args);
        } catch (ArgumentCountError $e) {
            throw new HttpNotFound($e->getMessage(), E_NOTICE, $e);
        }
    }


    /**
     * @param $route
     * @param $matches
     * @return mixed
     * @throws HttpNotFound
     */
    private static function startController($route, $matches)
    {
        $cmp = explode('@', $route['cmp']);
        //namespace
        self::setNameSpace($route['namespace']);
        self::setController(array_shift($cmp));
        self::setMethod(array_shift($cmp));
        self::setParams(array_merge($matches, $cmp));

        return self::route(self::$nameSpace . self::$controller, self::$method, self::$params);
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

        if (preg_match('#^' . $pattern . '$#u', $requestUri, $matches)) {
            return count($matches) > 1 ? array_slice($matches, 1) : array();
        }

        return false;
    }


    /**
     * @param string $nameSpace
     */
    private static function setNameSpace(string $nameSpace)
    {
        $controllerPath = str_replace('/', '\\', Config::get('path.controller')) . '\\';
        $subPath = $nameSpace ? str_replace('/', '\\', $nameSpace) . '\\' : '';
        $nameSpace = $controllerPath . $subPath;
        $nameSpace = preg_replace('/[^\w\d\\\]{1,256}/u', '', $nameSpace);
        self::$nameSpace = (string)$nameSpace;
    }

    /**
     * Girilen class kullanılabilir hale getirir
     * @param $className
     */
    private static function setController($className)
    {
        $className = preg_replace('/[^\w\d\/\\\]{1,256}/u', '', $className);
        self::$controller = $className ? str_replace('/', '\\', $className) : self::$controller;
    }

    /**
     * Girilen method kullanılabilir hale getirir
     *
     * @param $methodName
     */
    private static function setMethod($methodName)
    {
        $methodName = preg_replace('/[^\w\d]{1,256}/u', '', $methodName);
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
     * İsmi aranan routerları getirir
     * @param string $searchName name or regex
     * @return array
     */
    public static function getNames(string $searchName)
    {
        $routes = self::$routes;
        $matchedRoutes = [];
        array_walk($routes, function ($item) use ($searchName, &$matchedRoutes) {

            if (preg_match('#^' . $searchName . '$#', $item['name'])) {
                $matchedRoutes[] = $item;
            }
        });

        return $matchedRoutes;
    }


    /**
     *
     * @param string $routerName name or regex
     * @return bool
     */
    public static function matchName(string $routerName)
    {
        return preg_match('#^' . $routerName . '$#', self::$currentRoute['name']);
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
        /**
         * @var $view View
         **/
        $view = App::getInstance(View::class);
        if ($callback instanceof Closure) {
            self::$errors[$http_code] = $callback;
            return new static();
        }

        if (empty(self::$errors[$http_code])) {
            self::$errors[$http_code] = function () use ($view, $http_code) {
                $view->path('errors/' . $http_code, null)->render($http_code);
            };
        }

        return call_user_func(self::$errors[$http_code]);
    }
}
