<?php
/**
 * Uygulama başlatılmadan önce yüklenecek çıktı üretmeyen bileşenler.
 * Yükleme sırası function, alias, services şeklindedir.
 * {functions} fonksiyon veya php dosyalarını dahil eder.
 * {alias} uzun namespace veya sınıfların takma isimle çağırılmasını sağlar.
 * {services} ilgili services sınıfının boot methodunu çağırır. (bkz. Core\Services )
 */


return array(
    'functions' => array(
        'View' => ROOT . 'Helpers/functions/view_helper' . EXT,
        'Admin' => ROOT . 'Helpers/functions/admin_helper' . EXT,
    ),
    'alias' => array(
        'Router' => Core\Router\Router::class,
        'Request' => Core\Http\Request::class,
        'View' => Core\View::class,
        'Html' => Helpers\Html::class,
        'DB' => Core\Database\Database::class,
        'Lang' => Core\Language\Language::class,
        'Exceptions' => Core\Exceptions\Exceptions::class,
        'Config' => Core\Config\Config::class,
        'Auth' => Core\Auth\Auth::class,
        'Hook' => Core\Hook\Hook::class,
        'Session' => Core\Session\Session::class,
        'Cookie' => Core\Cookie\Cookie::class,
        'Cache' => Core\Cache\Cache::class,
        'Logger' => Core\Log\Logger::class
    ),
    'services' => array(
        'sessions' => Core\Services\SessionService::class,
        'language' => Core\Services\LanguageService::class,
        'csrf' => Core\Services\CsrfToken::class,
        //'rememberme' => Services\RememberMe::class
    ),
    'routes' => array(
        ROOT . 'routes/adminRouting' . EXT,
        ROOT . 'routes/routing' . EXT,
    )
);
