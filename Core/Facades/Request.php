<?php

namespace Core\Facades;

/**
 * @see \Core\Http\Request::path()
 * @method static string path()
 * --------------------------------------------------------------
 * @see \Core\Http\Request::requestUri()
 * @method static string requestUri()
 * --------------------------------------------------------------
 * @see \Core\Http\Request::matchUri()
 * @method static bool matchUri($uri)
 * --------------------------------------------------------------
 * @see \Core\Http\Request::currentUrl()
 * @method static string currentUrl()
 * --------------------------------------------------------------
 * @see \Core\Http\Request::baseUrl()
 * @method static string baseUrl()
 * --------------------------------------------------------------
 * @see \Core\Http\Request::segments()
 * @method static mixed segments(?int $key = null)
 * --------------------------------------------------------------
 * @see \Core\Http\Request::request()
 * @method static mixed request(string $name = null)
 * --------------------------------------------------------------
 * @see \Core\Http\Request::get()
 * @method static mixed get(string $name = null)
 * --------------------------------------------------------------
 * @see \Core\Http\Request::post()
 * @method static mixed post(string $name = null)
 * --------------------------------------------------------------
 * @see \Core\Http\Request::files()
 * @method static array files(string $name = null)
 * ---------------------------------------------------------------
 * @see \Core\Http\Request::raw()
 * @method static string raw()
 * ---------------------------------------------------------------
 * @see \Core\Http\Request::method()
 * @method static bool|string method(string $method = null)
 * ---------------------------------------------------------------
 * @see \Core\Http\Request::isAjax()
 * @method static bool isAjax(string $method = null)
 * ---------------------------------------------------------------
 * @see \Core\Http\Request::scheme()
 * @method static string scheme()
 * ---------------------------------------------------------------
 * @see \Core\Http\Request::host()
 * @method static string host()
 * ----------------------------------------------------------------
 * @see \Core\Http\Request::local()
 * @method static string local($basic = false)
 * ----------------------------------------------------------------
 * @see \Core\Http\Request::userAgent()
 * @method static string userAgent()
 * -----------------------------------------------------------------
 * @see \Core\Http\Request::referer()
 * @method static string referer()
 * -----------------------------------------------------------------
 * @see \Core\Http\Request::ip()
 * @method static string ip()
 * ----------------------------------------------------------------
 * @see \Core\Http\Request::forwardedIp()
 * @method static string forwardedIp()
 * -----------------------------------------------------------------
 * @see \Core\Http\Request::server()
 * @method static string server($value)
 * -----------------------------------------------------------------
 * @mixin \Core\Http\Request
 * @see \Core\Http\Request
 */
class Request extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \Core\Http\Request::class;
    }
}
