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
        'LogException' => Core\Log\LogException::class,
        'Config' => Core\Config\Config::class,
        'Auth' => Core\Auth\Auth::class,
        'Hook' => Core\Hook\Hook::class,
        'Session' => Core\Session\Session::class,
        'Cookie' => Core\Cookie\Cookie::class
    ),
    'services' => array(
        'sessions' => Services\SessionService::class,
        'language' => Services\LanguageService::class
    ),
    'routes' => array(
        ROOT . 'routes/adminRouting' . EXT,
        ROOT . 'routes/routing' . EXT,
    )
);
