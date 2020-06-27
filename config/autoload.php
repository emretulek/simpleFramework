<?php
/**
 * Uygulama başlatılmadan önce yüklenecek çıktı üretmeyen bileşenler.
 * {functions} fonksiyon veya php dosyalarını dahil eder.
 * {alias} uzun namespace veya sınıfların takma isimle çağırılmasını sağlar.
 * {services} ilgili services sınıfının boot methodunu çağırır. (bkz. Core\Services )
 */

return [
    'functions' => [

    ],
    'alias' => [
        'Router' => Core\Router\Router::class,
        'Request' => Core\Http\Request::class,
        'View' => Core\View::class,
        'DB' => Core\Database\DB::class,
        'Valid' => Core\Validation\Valid::class,
        'Lang' => Core\Language\Language::class,
        'Exceptions' => Core\Exceptions\Exceptions::class,
        'Config' => Core\Config\Config::class,
        'Auth' => Core\Auth\Auth::class,
        'Hook' => Core\Hook\Hook::class,
        'Session' => Core\Session\Session::class,
        'Cookie' => Core\Cookie\Cookie::class,
        'Cache' => Core\Cache\Cache::class,
        'Logger' => Core\Log\Logger::class,
        'Tag' => Helpers\Html\Tag::class,
        'Meta' => Helpers\Html\Meta::class,
        'Html' => Helpers\Html\Html::class,
    ],
    'services' => [
        'default' => Services\DefaultServices::class,
        'language' => Services\LanguageService::class,
        //'auth' => Services\AuthServices::class
    ],
    'routes' => [
        ROOT . 'routes/routing' . EXT,
    ]
];
