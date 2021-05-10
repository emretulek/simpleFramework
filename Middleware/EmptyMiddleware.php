<?php


namespace Middleware;


class EmptyMiddleware
{
    public function before()
    {

    }

    /**
     * Tüm methodlar Core\Http\Response döndürmek zorunda değil
     * bazı methodlar Core\View|String|Array gibi farklı tipler döndürebilir.
     * Dönen tipleri kendiniz ayarlamalısınız
     * @param $response
     */
    public function after($response)
    {

    }
}
