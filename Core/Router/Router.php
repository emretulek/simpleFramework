<?php

namespace Core\Router;

use Core\App;
use Core\Exceptions\ExceptionHandler;
use Core\Http\HttpMethodNotAllowed;
use Core\Http\HttpNotFound;
use Core\Http\Request;
use ArgumentCountError;
use Closure;
use Core\Http\Response;
use Core\Model\ModelException;
use Core\Reflection\Reflection;
use Core\View\View;
use Exception;
use ReflectionException;
use ReflectionMethod;
use TypeError;


class Router
{
    private App $app;
    private Request $request;

    private int $routeID = 0;
    private array $routes = [];
    private array $currentRoute = [];

    private array $methodPrefix = [];
    private array $methodNamespace = [];
    private array $methodMidleware = [];

    private string $prefix = '';
    private string $nameSpace = '';

    private string $controller = 'home';
    private string $method = 'index';
    private array $params = [];

    private array $errors = [];

    private array $matchPatterns = [
        '/*' => '/(.*)',
        '{id}' => '([0-9]+)',
        '{id?}' => '?([0-9]+)?',
        '{int}' => '([0-9\-]+)',
        '{int?}' => '?([0-9\-]+)?',
        '{string}' => '([\s\w\._-]+)',
        '{string?}' => '?([\s\w\._-]+)?',
        '{*}' => '(.+)',
        '{*?}' => '?(.*)?'
    ];


    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = $app->resolve(Request::class);
    }


    /**
     * @param array $options
     * @return Router
     */
    public function global(array $options): self
    {
        if (array_key_exists('middleware', $options)) {
            $this->methodMidleware[] = $options['middleware'];
        }

        if (array_key_exists('namespace', $options)) {
            $this->methodNamespace[] = $options['namespace'];
        }

        if (array_key_exists('prefix', $options)) {
            $this->methodPrefix[] = $options['prefix'];
        }

        return $this;
    }


    /**
     * @param array $options
     * @param callable $callback
     */
    public function group(array $options, callable $callback)
    {
        if (array_key_exists('middleware', $options)) {
            $this->methodMidleware[] = $options['middleware'];
        }

        if (array_key_exists('namespace', $options)) {
            $this->methodNamespace[] = $options['namespace'];
        }

        if (array_key_exists('prefix', $options)) {
            $this->methodPrefix[] = $options['prefix'];
        }

        call_user_func($callback, $options);

        if (array_key_exists('middleware', $options)) {
            array_pop($this->methodMidleware);
        }

        if (array_key_exists('namespace', $options)) {
            array_pop($this->methodNamespace);
        }

        if (array_key_exists('prefix', $options)) {
            array_pop($this->methodPrefix);
        }
    }


    /**
     * @param string $prefix
     * @return Router
     */
    public function prefix(string $prefix): self
    {
        if (!empty($this->routes[$this->routeID]['prefix'])) {
            $this->routes[$this->routeID]['prefix'] .= '/' . trim($prefix, '/');
        } else {
            $this->routes[$this->routeID]['prefix'] = trim($prefix, '/');
        }

        return $this;
    }


    /**
     * @param string $routeName
     * @return Router
     */
    public function name(string $routeName): self
    {
        $this->routes[$this->routeID]['name'] = $routeName;

        return $this;
    }


    /**
     * @param string $nameSpace
     * @return Router
     */
    public function nameSpace(string $nameSpace): self
    {
        if (isset($this->routes[$this->routeID]['namespace']) && !empty($this->routes[$this->routeID]['namespace'])) {

            $this->routes[$this->routeID]['namespace'] .= '\\' . trim($nameSpace, '\\');
        } else {
            $this->routes[$this->routeID]['namespace'] = trim($nameSpace, '\\');
        }

        return $this;
    }

    /**
     * @param $middleware
     * @return Router
     */
    public function middleware($middleware): self
    {
        if (is_array($middleware)) {
            $this->routes[$this->routeID]['middleware'] = array_merge($this->routes[$this->routeID]['middleware'], $middleware);
        } else {
            array_push($this->routes[$this->routeID]['middleware'], $middleware);
        }

        return $this;
    }

    /**
     * Yeni bir yönlendirme ekler.
     *
     * @param string $pattern yönlendirilecek istek deseni (regex)
     * @param string|callback $cmp İsteğin yönlendirileceği callback veya controller
     * @param null $method zorlanacak istek türü POST, GET
     * @return Router
     */
    private function addRoute(string $pattern, $cmp, $method): self
    {
        $this->routeID++;

        $this->routes[$this->routeID]['requestUri'] = $this->request->requestUri();
        $this->routes[$this->routeID]['pattern'] = $pattern;
        $this->routes[$this->routeID]['cmp'] = $cmp;
        $this->routes[$this->routeID]['method'] = $method;
        $this->routes[$this->routeID]['name'] = null;

        $this->routes[$this->routeID]['prefix'] = implode('/', $this->methodPrefix);
        $this->routes[$this->routeID]['namespace'] = implode('\\', $this->methodNamespace);
        $this->routes[$this->routeID]['middleware'] = [];

        foreach ($this->methodMidleware as $middleware) {
            if (is_array($middleware)) {
                $this->routes[$this->routeID]['middleware'] = array_merge($this->routes[$this->routeID]['middleware'], $middleware);
            } else {
                array_push($this->routes[$this->routeID]['middleware'], $middleware);
            }
        }

        return $this;
    }


    /**
     * otomatik route oluşturur
     *
     * @return Router
     */
    public function autoRute(): self
    {
        $segments = $this->request->segments();
        $segments = array_slice($segments, count($this->methodPrefix));
        $controller = isset($segments[0]) ? array_shift($segments) : null;
        $method = isset($segments[0]) ? array_shift($segments) : null;
        $method = preg_replace('/^_+/', '_', $method);
        $params = implode('/', array_map(fn($item) => '{*}', $segments));
        $params = $params ? '/'.$params : '';
        $pattern = rtrim('/' . $controller . '/' . $method, '/');
        $pattern = preg_replace("#[^\w\d/]#iu", "", $pattern);
        $pattern = preg_quote($pattern);

        return $this->any($pattern . $params, $controller . '@' . $method);
    }


    /**
     * @method Router::addRoute belirtilen tüm istek türlerini kabul eder
     *
     * @param $pattern
     * @param $cmp
     * @param array|string[] $methods ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS'] biri veya birkaçı
     * @return Router
     */
    public function useMethod($pattern, $cmp, array $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS']): self
    {
        return $this->addRoute($pattern, $cmp, $methods);
    }

    /**
     * @method Router::addRoute 'GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS' tüm istek türlerini kabul eder
     *
     * @param string $pattern Yönlendirilecek istek deseni (regex)
     * @param string|callback $cmp İsteğin yönlendirileceği callback veya controller
     * @return Router
     */
    public function any(string $pattern, $cmp): self
    {
        return $this->addRoute($pattern, $cmp, ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS']);
    }

    /**
     * @method Router::addRoute GET okuma
     *
     * @param string $pattern Yönlendirilecek istek deseni (regex)
     * @param string|callback $cmp İsteğin yönlendirileceği callback veya controller
     * @return Router
     */
    public function get(string $pattern, $cmp): self
    {
        return $this->addRoute($pattern, $cmp, ['GET']);
    }

    /**
     * @method Router::addRoute POST oluşturma
     *
     * @param string $pattern Yönlendirilecek istek deseni (regex)
     * @param string|callback $cmp İsteğin yönlendirileceği callback veya controller
     * @return Router
     */
    public function post(string $pattern, $cmp): self
    {
        return $this->addRoute($pattern, $cmp, ['POST']);
    }

    /**
     * @method Router::addRoute PUT güncelleme
     *
     * @param string $pattern Yönlendirilecek istek deseni (regex)
     * @param string|callback $cmp İsteğin yönlendirileceği callback veya controller
     * @return Router
     */
    public function put(string $pattern, $cmp): self
    {
        return $this->addRoute($pattern, $cmp, ['PUT']);
    }

    /**
     * @method Router::addRoute DELETE silme
     *
     * @param string $pattern Yönlendirilecek istek deseni (regex)
     * @param string|callback $cmp İsteğin yönlendirileceği callback veya controller
     * @return Router
     */
    public function delete(string $pattern, $cmp): self
    {
        return $this->addRoute($pattern, $cmp, ['DELETE']);
    }

    /**
     * @method Router::addRoute PATCH kısmi güncelleme
     *
     * @param string $pattern Yönlendirilecek istek deseni (regex)
     * @param string|callback $cmp İsteğin yönlendirileceği callback veya controller
     * @return Router
     */
    public function patch(string $pattern, $cmp): self
    {
        return $this->addRoute($pattern, $cmp, ['PATCH']);
    }

    /**
     * @method Router::addRoute HEAD üst bilgi
     *
     * @param string $pattern Yönlendirilecek istek deseni (regex)
     * @param string|callback $cmp İsteğin yönlendirileceği callback veya controller
     * @return Router
     */
    public function head(string $pattern, $cmp): self
    {
        return $this->addRoute($pattern, $cmp, ['HEAD']);
    }

    /**
     * @method Router::addRoute OPTIONS seçenekler
     *
     * @param string $pattern Yönlendirilecek istek deseni (regex)
     * @param string|callback $cmp İsteğin yönlendirileceği callback veya controller
     * @return self
     */
    public function options(string $pattern, $cmp): self
    {
        return $this->addRoute($pattern, $cmp, ['OPTIONS']);
    }


    /**
     * @param $controller
     * @param $method
     * @param $params
     * @return mixed
     * @throws HttpNotFound
     */
    private function route($controller, $method, $params)
    {
        try {
            $controller = $this->class_caseinsensitive($controller);
            //class mevcut değilse
            if (!class_exists($controller)) {
                throw new HttpNotFound($controller . ' : class not found.', E_NOTICE);
                //method mevcut değilse
            } elseif (!method_exists($controller, $method)) {
                throw new HttpNotFound($method . ' : method not found in ' . $controller, E_NOTICE);
            } else {
                //method public değilse
                $reflectionMethod = new ReflectionMethod($controller, $method);

                if (!$reflectionMethod->isPublic()) {
                    throw new HttpNotFound($method . ' : method is not a public', E_NOTICE);
                }

                $reflectionForParameters = new Reflection($this->app);
                $classRefParameters = $reflectionForParameters->setMethodParameters($controller, $method, $params);
            }

            return call_user_func_array([new $controller, $method], $classRefParameters);

        } catch (ArgumentCountError | TypeError $e) {//TODO badrequest olarak değişecek
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
    public function start()
    {
        try {

            foreach ($this->routes as $name => $route) {

                if (false !== ($matches = $this->rootMatch($route['prefix'] . $route['pattern'], $route['requestUri']))) {

                    //current router
                    $this->currentRoute = $route;

                    //prefix
                    $this->prefix = $route['prefix'];

                    //Request method check
                    if (in_array($this->request->method(), $route['method']) == false) {
                        throw new HttpMethodNotAllowed("Http method allowed " . implode(",", $route['method']));
                    }

                    //load middlewares before
                    foreach ($route['middleware'] as $middleware) {
                        if (is_callable([$middleware, 'before'])) {
                            call_user_func([new $middleware, 'before']);
                        }
                    }

                    //Router callback clouser
                    if (is_callable($route['cmp'])) {
                        $response = $this->startCallback($route['cmp'], $matches);
                    } else {
                        $response = $this->startController($route, $matches);
                    }

                    //load middleware after
                    foreach ($route['middleware'] as $middleware) {
                        if (is_callable([$middleware, 'after'])) {
                            $response = call_user_func([new $middleware, 'after'], $response);
                        }
                    }

                    //if response view or response class
                    if ($response instanceof View || $response instanceof Response) {
                        echo $response;
                    } else {
                        echo new Response($response);
                    }

                    return;
                }
            }

            throw new HttpNotFound('No match router.', E_NOTICE);

        } catch (ModelException $e) {
            $this->app->debug($e);
            return ;
        }catch (HttpNotFound $e) {
            $this->app->debug($e);
            return self::errors(404);
        } catch (HttpMethodNotAllowed $e) {
            $this->app->debug($e);
            return self::errors(405);
        } catch (Exception $e) {
            if(array_key_exists($e->getCode(), ExceptionHandler::NOTICE)){
                $this->app->debug($e);
            }else{
                $this->app->debug($e);
                return self::errors(500);
            }
        }
    }


    /**
     * @param callable $callback
     * @param $args
     * @return Response|View|null
     * @throws HttpNotFound|ModelException
     */
    private function startCallback(callable $callback, $args)
    {
        try {
            $reflection = new Reflection($this->app);
            $parameters = $reflection->setFunctionParameters($callback, $args);

            return call_user_func_array($callback, $parameters);
        } catch (ArgumentCountError $e) {
            throw new HttpNotFound($e->getMessage(), E_NOTICE, $e);
        }
    }


    /**
     * @param $route
     * @param $matches
     * @return mixed
     * @throws HttpNotFound|ModelException
     */
    private function startController($route, $matches)
    {
        $cmp = explode('@', $route['cmp']);
        //namespace
        $this->setNameSpace($route['namespace']);
        $this->setController(array_shift($cmp));
        $this->setMethod(array_shift($cmp));
        $this->setParams(array_merge($matches, $cmp));

        return $this->route($this->nameSpace . $this->controller, $this->method, $this->params);
    }


    /**
     * @param $pattern
     * @param $replacement
     * @return $this
     */
    public function where($pattern, $replacement): self
    {
        $this->matchPatterns[$pattern] = $replacement;
        return $this;
    }

    /**
     * Yönlendirme kalıplarını regex olarak dönüştürür.
     *
     * @param string $pattern
     * @param $requestUri
     * @return array|bool
     */
    private function rootMatch(string $pattern, $requestUri)
    {
        $patternKeys = array_keys($this->matchPatterns);
        array_walk($patternKeys, function (&$item) {
            $item = '#' . preg_quote($item) . '#';
        });

        $pattern = preg_replace($patternKeys, $this->matchPatterns, $pattern);
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
    private function setNameSpace(string $nameSpace)
    {
        $controllerPath = str_replace('/', '\\', $this->app->config['path']['controller']) . '\\';
        $subPath = $nameSpace ? str_replace('/', '\\', $nameSpace) . '\\' : '';
        $nameSpace = $controllerPath . $subPath;
        $nameSpace = preg_replace('/[^\w\d\\\]{1,256}/u', '', $nameSpace);
        $this->nameSpace = (string)$nameSpace;
    }

    /**
     * Girilen class kullanılabilir hale getirir
     * @param $className
     */
    private function setController($className)
    {
        $className = preg_replace('/[^\w\d\/\\\]{1,256}/u', '', $className);
        $this->controller = $className ? str_replace('/', '\\', $className) : $this->controller;
    }

    /**
     * Girilen method kullanılabilir hale getirir
     *
     * @param $methodName
     */
    private function setMethod($methodName)
    {
        $methodName = preg_replace('/[^\w\d]{1,256}/u', '', $methodName);
        $this->method = $methodName ? $methodName : $this->method;
    }

    /**
     * Girilen parametreleri kullanılabilir hale getirir
     *
     * @param $params
     */
    private function setParams($params)
    {
        if (is_array($params)) {
            $this->params = $params;
        } elseif (empty($params)) {
            $this->params = [];
        } else {
            $this->params = [$params];
        }

        $this->params = array_map(function ($param){
            return trim(strip_tags($param));
        }, $this->params);
    }

    /**
     * Aktif namespace döndürür
     *
     * @return string
     */
    public function getNameSpace(): string
    {
        return $this->nameSpace;
    }

    /**
     * Aktif controller döndürür
     *
     * @return string
     */
    public function getController(): string
    {
        return $this->controller;
    }

    /**
     * Aktif Method döndürür
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Aktif parametreleri döndürür
     *
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Aktif dizini döndürür /admin için admin gibi.
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return trim($this->prefix, '/');
    }


    /**
     * İsmi aranan routerları getirir
     * @param string $searchName name or regex
     * @return array
     */
    public function getNames(string $searchName): array
    {
        $matchedRoutes = [];
        array_walk($this->routes, function ($item) use ($searchName, &$matchedRoutes) {

            if (preg_match('#^' . $searchName . '$#', $item['name'])) {
                if($item['cmp'] instanceof Closure){
                    $item['cmp'] = 'Clouser';
                }
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
    public function matchName(string $routerName): bool
    {
        return preg_match('#^' . $routerName . '$#', $this->currentRoute['name']);
    }

    /**
     * @return View
     */
    public function view():View
    {
        return $this->app->resolve(View::class);
    }

    /**
     * Router custom errors
     *
     * @param $http_code
     * @param callable|null $callback
     * @return mixed
     */
    public function errors($http_code, callable $callback = null)
    {
        if ($callback instanceof Closure) {
            $this->errors[$http_code] = $callback;
            return $this;
        }

        if (empty($this->errors[$http_code])) {
            $this->errors[$http_code] = function () use ($http_code) {
                return $this->view()->path('errors/' . $http_code, null)->render($http_code);
            };
        }

        return call_user_func($this->errors[$http_code]);
    }


    /**
     * Büyük küçük harf duyarlı işltemim sistemlerinde sınıfın olduğu dizini tarayıp
     * büyük küçük harf duyarsız olarak sınıfı yükler.
     * @param $class
     * @return string
     */
    protected function class_caseinsensitive($class): string
    {
        $nameSpacePartition = explode('\\', $class);
        $nameSpacePartition = array_map('ucfirst', $nameSpacePartition);
        $fileName = array_pop($nameSpacePartition) . EXT;
        $classDirectory = implode(DS, array_filter($nameSpacePartition));
        $files = glob(ROOT . DS . $classDirectory . DS . "*" . EXT);

        if ($filePath = current(preg_grep("#" . DS . $fileName . "$#iu", $files))) {
            $pathInfo = pathinfo(realpath($filePath));
            $filePathWithoutExtension = $pathInfo['dirname'] . DS . $pathInfo['filename'];
            $classPath = preg_replace('#' . preg_quote(ROOT) . '#', '', $filePathWithoutExtension, 1);
            return str_replace('/', '\\', $classPath);
        }

        return $class;
    }
}
