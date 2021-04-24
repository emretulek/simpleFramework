<?php

namespace Core\Facades;

/**
 * @see \Core\View\View::insertData()
 * @method static array insertData(array $data)
 * ----------------------------------------------------------------------------------
 * @see \Core\View\View::page()
 * @method static \Core\View\View page(string $fileName, array $data = array())
 * ----------------------------------------------------------------------------------
 * @see \Core\View\View::path()
 * @method static \Core\View\View path(string $filePath, $data = array(), $ext = EXT)
 * -----------------------------------------------------------------------------------
 * @see \Core\View\View::layout()
 * @method static \Core\View\View layout(string $fileName, $data = array())
 * ----------------------------------------------------------------------------------
 * @see \Core\View\View::getLayoutPage()
 * @method static \Core\View\View getLayoutPage()
 * ----------------------------------------------------------------------------------
 * @see \Core\View\View::json()
 * @method static \Core\Http\Response json($data)
 * ------------------------------------------------------------------------------------
 * @see \Core\View\View::render()
 * @method static void render(int $code = 200, array $headers = [])
 * ------------------------------------------------------------------------------------
 * @see \Core\View\View::getBuffer()
 * @method static string getBuffer($clear = true)
 * ------------------------------------------------------------------------------------
 * @see \Core\View\View::setLayout()
 * @method static \Core\View\View setLayout(string $layout)
 * -------------------------------------------------------------------------------------
 * @see \Core\View\View::response()
 * @method static \Core\Http\Response response(int $code = 200, array $headers = [])
 * -------------------------------------------------------------------------------------
 * @mixin \Core\View\View
 * @see \Core\View\View
 */
class View extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \Core\View\View::class;
    }
}
