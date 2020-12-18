<?php 
/**
 * @Created 08.12.2020 15:46:48
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class View
 * @package Core\Facades
 */


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
 * @see \Core\View\View::json()
 * @method static \Core\View\View json($data)
 * ------------------------------------------------------------------------------------
 * @see \Core\View\View::render()
 * @method static \Core\View\View render($code = null, $headers = null)
 * ------------------------------------------------------------------------------------
 * @see \Core\View\View::getBuffer()
 * @method static \Core\View\View getBuffer()
 * ------------------------------------------------------------------------------------
 * @see \Core\View\View::setLayout()
 * @method static \Core\View\View setLayout(string $layout)
 * -------------------------------------------------------------------------------------
 * @mixin \Core\View\View
 * @see \Core\View\View
 */

class View extends Facade {

    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \Core\View\View::class;
    }
}
