<?php
/**
 * @Created 04.12.2020 01:53:47
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class Reflection
 * @package Core\Reflection
 */


namespace Core\Reflection;


use Core\App;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;

class Reflection
{

    private App $app;

    /**
     * Reflection constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }


    /**
     * @param $class
     * @param array $args
     * @return array
     */
    public function setClassParameters($class, $args = []): array
    {
        return $this->setMethodParameters($class, '__construct', $args);
    }


    /**
     * @param $class
     * @param $method
     * @param array $args
     * @return array
     */
    public function setMethodParameters($class, $method, $args = []): array
    {
        $definedParameters = [];
        $parameters = $this->getMethodParameters($class, $method);

        foreach ($parameters as $key => $parameter) {

            if (class_exists($parameter)) {
                if ($this->app->isBinding($parameter)) {
                    $definedParameters[$key] = $this->app->resolve($parameter);
                } else {
                    //tanımlanmamış parametre içermeyen sınıflar
                    $definedParameters[$key] = new $parameter();
                }
            } else {
                if (!empty($args)) {
                    $definedParameters[$key] = array_shift($args);
                }
            }
        }

        return $definedParameters;
    }


    /**
     * Class method
     * @param string $class
     * @param string $method
     * @return array|string[]
     */
    public function getMethodParameters(string $class, string $method): array
    {
        $parameterType = [];
        try {
            $refMethod = new ReflectionMethod($class, $method);
            $parameters = $refMethod->getParameters();
            $parameterType = [];

            foreach ($parameters as $parameter) {

                $parameterType[] = $parameter->getType() ? $parameter->getType()->getName() : '';
            }
        } catch (ReflectionException $e) {
            $this->app->debug($e);
        }

        return $parameterType;
    }


    /**
     * @param $function
     * @param array $args
     * @return array
     */
    public function setFunctionParameters($function, $args = []): array
    {
        $definedParameters = [];
        $parameters = $this->getFunctionParameter($function);

        foreach ($parameters as $key => $parameter) {

            if ($parameter && class_exists($parameter)) {
                if ($this->app->isBinding($parameter)) {
                    $definedParameters[$key] = $this->app->resolve($parameter);
                } else {
                    //tanımlanmamış ve parametre içermeyen sınıflar
                    $definedParameters[$key] = new $parameter();
                }
            } else {
                if (!empty($args)) {
                    $definedParameters[$key] = array_shift($args);
                }
            }
        }

        return $definedParameters;
    }


    /**
     * function
     * @param string|object $function
     * @return array
     */
    public function getFunctionParameter($function): array
    {
        $parameterType = [];

        try {
            $refMethod = new ReflectionFunction($function);
            $parameters = $refMethod->getParameters();
            $parameterType = [];

            foreach ($parameters as $parameter) {

                $parameterType[] = $parameter->getType() ? $parameter->getType()->getName() : '';
            }
        } catch (ReflectionException $e) {
            $this->app->debug($e);
        }

        return $parameterType;
    }
}
