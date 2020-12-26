<?php
/**
 * @Created 01.12.2020 01:08:43
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class App
 * @package Core
 */


namespace Core;


use ArrayAccess;
use Closure;
use Core\Config\LoadConfigFiles;
use Core\Exceptions\ExceptionHandler;
use Core\Facades\Facade;
use Core\Router\Router;
use Core\Services\ServiceProvider;
use Exception;
use ReflectionClass;
use RuntimeException;
use Throwable;

class App implements ArrayAccess
{
    /**
     * semantic version
     */
    const VERSION = "3.0.1";

    /**
     * @var static
     */
    protected static App $instance;

    /**
     * @var bool
     */
    protected bool $boot = false;

    /**
     * @var array
     */
    protected array $bindings = [];

    /**
     * class instances
     * @var array|object[]
     */
    protected array $instances = [];

    /**
     * default aliases
     * @var array|string[]
     */
    public array $aliases = [];

    /**
     * default files
     * @var array|string[]
     */
    protected array $files = [];

    /**
     * default services
     * @var array|string[]
     */
    protected array $services = [
        \Core\Config\ConfigServiceProvider::class,
        \Core\Http\RequestServiceProvider::class,
        \Core\Session\SessionServiceProvider::class,
        \Core\Cookie\CookieServiceProvider::class,
        \Core\Router\RouterServiceProvider::class,
        \Core\View\ViewServiceProvider::class,
        \Core\Database\DatabaseServiceProvider::class,
        \Core\Language\LanguageServiceProvider::class,
        \Core\Cache\CacheServiceProvider::class,
        \Core\Validation\FilterServiceProvider::class,
        \Core\Crypt\CryptServiceProvider::class,
        \Core\Csrf\CsrfServiceProvider::class,
        \Core\Log\LoggerServiceProvider::class,
        \Core\Auth\AuthServiceProvider::class,
        \Core\Hook\HookServiceProvider::class,
    ];

    /**
     * route files
     * @var array|string[]
     */
    protected array $routes = [];

    /**
     * @var string $basePath ROOT
     */
    public string $basePath = '';

    /**
     * @var string $configPath
     */
    public string $configPath = '/config';

    /**
     * @var array $config
     */
    public array $config;


    /**
     * App constructor.
     * @param string $basePath
     */
    public function __construct($basePath = '')
    {
        $this->basePath = $basePath;

        $this->boot();

        try {
            //yükleme sırası önemlidir değiştirilmemeli
            $this->loadExceptionHandler();
            $this->loadFiles($this->config['autoload']['files']);
            $this->loadServices($this->config['autoload']['services']);
            $this->loadAlias($this->config['autoload']['aliases']);
            $this->loadFacades();
            $this->loadRoutes($this->config['autoload']['routes']);
        }catch (Exception $e){
            $this->debug($e);
        }
    }

    /**
     * @return void
     */
    public function boot()
    {
        if($this->boot){
            return;
        }

        $this->basePath = rtrim($this->basePath, '\/');
        $this->configPath = $this->basePath . $this->configPath;

        $this->loadConfiguration($this->configPath);

        static::$instance = $this;
        $this->instance('app', $this);

        $this->boot = true;
    }


    /**
     * @param string $path
     * @return array
     */
    protected function loadConfiguration(string $path):array
    {
        $configs = new LoadConfigFiles($path);

        return $this->config = $configs->loadFiles();
    }

    /**
     * App self instance
     * @return static
     */
    public static function getInstance():App
    {
        return static::$instance ??= new static();
    }


    /**
     * @param string $className
     * @return mixed|object
     */
    public function resolve(string $className):object
    {
        if(array_key_exists($className, $this->instances)){
            return $this->instances[$className];
        }

        if($this->isBinding($className)){
            if($this->bindings[$className]['shared']){
                return $this->instances[$className] = $this->bindings[$className]['closure']($this);
            }else{
                return $this->bindings[$className]['closure']($this);
            }
        }

        throw new RuntimeException("{$className} Sınıf çözülemiyor veya uygulamaya kayıt edilmedi.");
    }


    /**
     * @param string $name
     * @param Closure|null $closure
     */
    public function singleton(string $name, Closure $closure = null)
    {
        $this->bind($name, $closure, true);
    }


    /**
     * @param string $name
     * @param Closure|null $closure
     * @param bool $shared false
     */
    public function bind(string $name, Closure $closure = null, bool $shared = false)
    {
        if (is_null($closure)) {
            $closure = function () use ($name){
                return new $name;
            };
        }

        $this->bindings[$name] = [
            'closure' => $closure,
            'shared' => $shared
        ];
    }


    /**
     * @param string $name
     * @return bool
     */
    public function isBinding(string $name):bool
    {
        return array_key_exists($name, $this->bindings);
    }


    /**
     * @deprecated
     * @param string $className
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function callBindingClassWithMethod(string $className, string $method, array $args = [])
    {
        if($instance = $this->resolve($className)){

            $reflection = new Reflection\Reflection($this);
            $methodParam = $reflection->setMethodParameters($className, $method, $args);

            return $instance->$method(...$methodParam);
        }

        throw new RuntimeException("Sınıf çözülemiyor veya kayıt edilmeiş.");
    }


    /**
     * @param string $name
     * @param $instance
     * @return mixed
     */
    public function instance(string $name, $instance)
    {
        return $this->instances[$name] = $instance;
    }


    /**
     * @param $id
     * @return bool
     */
    public function has($id): bool
    {
        return isset($this->instances[$id], $this->bindings[$id]);
    }


    /**
     * get instance from name
     * @param $id
     * @return mixed
     * @throws Exception
     */
    public function get($id)
    {
        if ($this->has($id)) {

            return $this->instances[$id] ?? $this->resolve($id);
        }

        throw new Exception("Instance [{$id}] not found in app");
    }


    /**
     * Sınıf takma isimlerini yüklenmesi
     * @param array|string[] $aliases [alias => className]
     */
    protected function loadAlias(array $aliases)
    {
        $this->aliases += $aliases;

        foreach ($this->aliases as $alias => $orginal) {

            class_alias($orginal, $alias);
        }
    }


    /**
     * projeden önce gereken dosyaların yüklenmesi
     * @param array|string $files path
     */
    protected function loadFiles(array $files)
    {
        $this->files += $files;

        foreach ($this->files as $file) {

            if (is_file($file)) {
                require_once realpath($file);
            }
        }
    }


    /**
     * @param array|string[] $services services class Name
     */
    protected function loadServices(array $services)
    {
       $this->services += $services;

        try {
            foreach ($this->services as $serviceProvider) {

                $reflectionClass = new ReflectionClass($serviceProvider);

                if ($reflectionClass->isSubclassOf(ServiceProvider::class)) {
                    $serviceProviderInstance = new $serviceProvider($this);
                    $serviceProviderInstance->register();
                    $serviceProviderInstance->boot();
                } else {
                    throw new Exception("{$serviceProvider} sınıfı " . ServiceProvider::class . " sınıfından türetilmedilir.", E_WARNING);
                }
            }
        } catch (Exception $e) {
            $this->debug($e);
        }
    }

    /**
     *
     */
    protected function loadFacades()
    {
        Facade::setFacadeApplication($this);
    }


    /**
     * @param array|string[] $routes route files
     */
    protected function loadRoutes(array $routes)
    {
        $this->routes += $routes;

        foreach ($this->routes as $route) {
            if (is_file($route)) {
                require_once realpath($route);
            }
        }

        $this->resolve(Router::class)->start();
    }

    /**
     * Exception hadnler sınıfı yüklenmesi
     */
    protected function loadExceptionHandler()
    {
        $this->instance(ExceptionHandler::class, new ExceptionHandler());
    }


    /**
     * @param Throwable $throwable
     */
    public function debug(Throwable $throwable)
    {
        $this->resolve(ExceptionHandler::class)->debug($throwable);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this[$name];
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this[$name] = $value;
    }


    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset):bool
    {
        return $this->has($offset);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset): object
    {
        return $this->resolve($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->bind($offset, $value instanceof Closure ? $value : function () use ($value) {
            return $value;
        });
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->bindings[$offset], $this->instances[$offset]);
    }
}
