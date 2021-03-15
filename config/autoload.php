<?php
/**
 * Uygulama başlatılmadan önce yüklenecek çıktı üretmeyen bileşenler.
 * {functions} fonksiyon veya php dosyalarını dahil eder.
 * {alias} uzun namespace veya sınıfların takma isimle çağırılmasını sağlar.
 * {services} ilgili services sınıfının boot methodunu çağırır. (bkz. Core\ServiceProvider )
 * {routes} route dosyalarının tam yolu
 */

return [
    'files' => [
        //uygulama başlatılmadan önce yüklenecek dosyalar
    ],
    'aliases' => [
        //static class alies
        'valid' => Core\Validation\Valid::class,
        //facade alias
        'config' => Core\Facades\Config::class,
        'router' => Core\Facades\Router::class,
        'request' => Core\Facades\Request::class,
        'session' => Core\Facades\Session::class,
        'cookie' => Core\Facades\Cookie::class,
        'view' => Core\Facades\View::class,
        'db' => Core\Facades\DB::class,
        'lang' => Core\Facades\Lang::class,
        'auth' => Core\Facades\Auth::class,
        'hook' => Core\Facades\Hook::class,
        'cache' => Core\Facades\Cache::class,
        'logger' => Core\Facades\Logger::class,
        'hash' => \Core\Facades\Hash::class,
        'csrf' => \Core\Facades\Csrf::class,
        //helper alies
        'tag' => Helpers\Html\Tag::class,
        'meta' => Helpers\Html\Meta::class,
        'html' => Helpers\Html\Html::class,
    ],
    'services' => [
        //'default' => Services\DefaultServiceProvider::class,
        //'language' => Services\LanguageService::class,
    ],
    'routes' => [
        ROOT . '/routes/routing' . EXT,
    ]
];
