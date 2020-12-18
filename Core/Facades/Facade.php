<?php 
/**
 * @Created 03.12.2020 00:11:27
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class Facade
 * @package Core\Facades
 */


namespace Core\Facades;


use Core\App;
use RuntimeException;
use stdClass;

abstract class Facade {

    /**
     * @var App
     */
    protected static App $app;

    /**
     * @var array|object[]
     */
    protected static array $resolvedInstance;

    /**
     * @return string
     */
    abstract protected static function getFacadeAccessor():string;

    /**
     * {@inheritDoc}
     */
    public static function setFacadeApplication(App $app)
    {
        static::$app = $app;
    }


    /**
     * @param string $mainClass
     * @return object
     */
    protected static function resolveFacadeInstance(string $mainClass):object
    {
        if (isset(static::$resolvedInstance[$mainClass])) {
            return static::$resolvedInstance[$mainClass];
        }

        if ($instance = static::$app->resolve($mainClass)) {
            return $instance;
        }

        return (object) null;
    }


    /**
     * @return object|stdClass
     */
    protected static function getFacadeRoot()
    {
        return static::resolveFacadeInstance(static::getFacadeAccessor());
    }


    /**
     * clear
     */
    public static function clearResolvedInstances()
    {
        static::$resolvedInstance = [];
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     * @throws \Exception
     */
    public static function __callStatic($method, $args)
    {
        $instance = static::getFacadeRoot();

        if (! $instance) {
            throw new RuntimeException('A facade root has not been set.');
        }

        return $instance->$method(...$args);
    }
}
