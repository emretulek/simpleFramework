<?php

namespace Core\Services;

/**
 * Class Services
 * Servisler bu sınıftan türetilir, türetilen sınıfların boot methodu otomatik çalıştırılır.
 * Aktif edilmek istenen servisler, config/autolad.php dosyasına eklenmelidir.
 */
abstract class Services
{

    /**
     * boot methodu proje başlatılmadan önce çalıştırılacak kodları içermeli ve çıktı vermemelidir.
     * @return mixed
     */
    abstract protected function boot();

    /**
     * Services constructor.
     */
    public function __construct()
    {
        $this->boot();
    }
}

