<?php

namespace Core\Facades;

/**
 * @see \Core\Router\Router::global()
 * @method static \Core\Router\Router global(array $options)
 * --------------------------------------------------------------
 * @see \Core\Router\Router::group()
 * @method static void group(array $options, callable $callback)
 * ------------------------------------------------------------------
 * @see \Core\Router\Router::prefix()
 * @method static \Core\Router\Router prefix(string $prefix)
 * ---------------------------------------------------------------
 * @see \Core\Router\Router::name()
 * @method static \Core\Router\Router name(string $routeName)
 * ----------------------------------------------------------------
 * @see \Core\Router\Router::nameSpace()
 * @method static \Core\Router\Router nameSpace(string $nameSpace)
 * --------------------------------------------------------------------------
 * @see \Core\Router\Router::middleware()
 * @method static \Core\Router\Router middleware($middleware)
 * -------------------------------------------------------------------------
 * @see \Core\Router\Router::autoRute()
 * @method static \Core\Router\Router autoRute()
 * --------------------------------------------------------------------------
 * @see \Core\Router\Router::useMethod()
 * @method static \Core\Router\Router useMethod($pattern, $cmp, array $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS'])
 * --------------------------------------------------------------------------
 * @see \Core\Router\Router::any()
 * @method static \Core\Router\Router any(string $pattern, $cmp)
 * --------------------------------------------------------------------------
 * @see \Core\Router\Router::get()
 * @method static \Core\Router\Router get(string $pattern, $cmp)
 * --------------------------------------------------------------------------
 * @see \Core\Router\Router::post()
 * @method static \Core\Router\Router post(string $pattern, $cmp)
 * -------------------------------------------------------------------------
 * @see \Core\Router\Router::put()
 * @method static \Core\Router\Router put(string $pattern, $cmp)
 * --------------------------------------------------------------------------
 * @see \Core\Router\Router::delete()
 * @method static \Core\Router\Router delete(string $pattern, $cmp)
 * --------------------------------------------------------------------------
 * @see \Core\Router\Router::patch()
 * @method static \Core\Router\Router patch(string $pattern, $cmp)
 * --------------------------------------------------------------------------
 * @see \Core\Router\Router::head()
 * @method static \Core\Router\Router head(string $pattern, $cmp)
 * ---------------------------------------------------------------------------
 * @see \Core\Router\Router::options()
 * @method static \Core\Router\Router options(string $pattern, $cmp)
 * --------------------------------------------------------------------------
 * @see \Core\Router\Router::where()
 * @method static \Core\Router\Router where($pattern, $replacement)
 * --------------------------------------------------------------------------
 * @see \Core\Router\Router::getNameSpace()
 * @method static string getNameSpace()
 * ---------------------------------------------------------------------------
 * @see \Core\Router\Router::getController()
 * @method static string getController()
 * --------------------------------------------------------------------------
 * @see \Core\Router\Router::getMethod()
 * @method static string getMethod()
 * ---------------------------------------------------------------------------
 * @see \Core\Router\Router::getParams()
 * @method static string getParams()
 * ----------------------------------------------------------------------------
 * @see \Core\Router\Router::getPrefix()
 * @method static string getPrefix()
 * ---------------------------------------------------------------------------
 * @see \Core\Router\Router::getNames()
 * @method static array getNames(string $searchName)
 * ---------------------------------------------------------------------------
 * @see \Core\Router\Router::matchName()
 * @method static bool matchName(string $routerName)
 * --------------------------------------------------------------------------
 * @see \Core\Router\Router::errors()
 * @method static void errors($http_code, callable $callback = null)
 * ----------------------------------------------------------------------------
 * @mixin \Core\Router\Router
 * @see \Core\Router\Router
 */
class Router extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \Core\Router\Router::class;
    }
}
