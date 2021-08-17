<?php

namespace Core;

use Closure;
use Core\Auth\AuthServiceProvider;
use Core\Cache\CacheServiceProvider;
use Core\Config\ConfigServiceProvider;
use Core\Config\LoadConfigFiles;
use Core\Cookie\CookieServiceProvider;
use Core\Crypt\CryptServiceProvider;
use Core\Csrf\CsrfServiceProvider;
use Core\Database\DatabaseServiceProvider;
use Core\Era\EraServiceProvider;
use Core\Exceptions\ExceptionHandler;
use Core\Facades\Facade;
use Core\Hook\HookServiceProvider;
use Core\Http\HttpServiceProvider;
use Core\Language\LanguageServiceProvider;
use Core\Log\LoggerServiceProvider;
use Core\Router\Router;
use Core\Router\RouterServiceProvider;
use Core\Services\ServiceProvider;
use Core\Session\SessionServiceProvider;
use Core\Validation\FilterServiceProvider;
use Core\View\ViewServiceProvider;
use Exception;
use ReflectionClass;
use RuntimeException;
use Throwable;

class App
{
    /**
     * semantic version
     */
    const VERSION = "3.5.3";

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
    protected array $providers = [
        ConfigServiceProvider::class,
        EraServiceProvider::class,
        CryptServiceProvider::class,
        HttpServiceProvider::class,
        DatabaseServiceProvider::class,
        CookieServiceProvider::class,
        SessionServiceProvider::class,
        LanguageServiceProvider::class,
        CacheServiceProvider::class,
        FilterServiceProvider::class,
        CsrfServiceProvider::class,
        AuthServiceProvider::class,
        LoggerServiceProvider::class,
        HookServiceProvider::class,
        ViewServiceProvider::class,
        RouterServiceProvider::class,
    ];

    /**
     * @var array
     */
    public array $registeredProviders = [];

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

        $this->basePath = rtrim($this->basePath, '\/');
        $this->configPath = $this->basePath . $this->configPath;

        $this->loadConfiguration($this->configPath);

        static::$instance = $this;
        $this->instance('app', $this);
    }

    /**
     * @return void
     */
    public function boot()
    {
        if ($this->boot) {
            return;
        }

        try {
            //yükleme sırası önemlidir değiştirilmemeli
            $this->loadExceptionHandler();
            $this->loadFiles($this->config['autoload']['files']);
            $this->loadFacades();
            $this->loadAlias($this->config['autoload']['aliases']);
            $this->registerProviders($this->config['autoload']['services']);
            $this->bootProviders();
            $this->loadRoutes($this->config['autoload']['routes']);
        } catch (Exception $e) {
            $this->debug($e);
        }

        $this->boot = true;
    }

    /**
     * App self instance
     * @return static
     */
    public static function getInstance(): App
    {
        return static::$instance ??= new static();
    }

    /**
     * @param string $path
     * @return array
     */
    protected function loadConfiguration(string $path): array
    {
        $configs = new LoadConfigFiles($path);

        return $this->config = $configs->loadFiles();
    }

    /**
     * @param string $className
     * @return mixed|object
     */
    public function resolve(string $className): object
    {
        if (isset($this->instances[$className])) {
            return $this->instances[$className];
        }

        if ($this->isBinding($className)) {

            if ($this->bindings[$className]['shared']) {
                return $this->instances[$className] = $this->bindings[$className]['closure']($this);
            } else {
                return $this->bindings[$className]['closure']($this);
            }
        }

        throw new RuntimeException("$className Sınıf çözülemiyor veya uygulamaya kayıt edilmedi.");
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
            $closure = function () use ($name) {
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
    public function isBinding(string $name): bool
    {
        return array_key_exists($name, $this->bindings);
    }

    /**
     * @param string $className
     * @param string $method
     * @param array $args
     * @return mixed
     * @deprecated
     */
    public function callBindingClassWithMethod(string $className, string $method, array $args = [])
    {
        if ($instance = $this->resolve($className)) {

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
     * @throws RuntimeException
     */
    public function get($id)
    {
        if ($this->has($id)) {

            return $this->instances[$id] ?? $this->resolve($id);
        }

        throw new RuntimeException("Instance [$id] not found in app");
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
     * @param array|string[] $providers services class Name
     */
    protected function registerProviders(array $providers)
    {
        $this->providers += $providers;

        foreach ($this->providers as $service) {

            try {
                $reflectionClass = new ReflectionClass($service);

                if ($reflectionClass->isSubclassOf(ServiceProvider::class)) {
                    $providerInstance = new $service($this);
                    $providerInstance->register();
                    $this->registeredProviders[$service] = $providerInstance;
                } else {
                    throw new RuntimeException("$service sınıfı " . ServiceProvider::class . " sınıfından türetilmedilir.", E_WARNING);
                }

            } catch (RuntimeException $e) {
                $this->debug($e);
            }
        }
    }

    /**
     * boot method registered service providers
     */
    protected function bootProviders()
    {
        foreach ($this->registeredProviders as $registeredProvider) {
            $registeredProvider->boot();
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
     * @return string[]
     */
    public function __debugInfo()
    {
        return [];
    }
}
