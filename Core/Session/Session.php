<?php

namespace Core\Session;

use Core\App;
use Core\Http\Request;

class Session
{

    private array $config;

    public function __construct(SessionManager $manager, App $app)
    {
        $this->config = $app->config['session'];

        if (!$this->status()) {

            session_set_save_handler($manager->getDefaultDriver(), true);

            if(!empty($this->config['cookie_params'])) {

                if(isset($this->config['cookie_params']['secure']) && $this->config['cookie_params']['secure'] == null){
                    $this->config['cookie_params']['secure'] = $app->resolve(Request::class)->scheme() == 'https';
                }

                session_set_cookie_params($this->config['cookie_params']);
            }
        }
    }


    /**
     * @return bool
     */
    public function start(): bool
    {
        if (!$this->status()) {

            $started = session_start($this->config['options']);
            $this->tempClear();

            return $started;
        }

        return false;
    }


    /**
     * Yeni bir oturum verisi oluşturur.
     *
     * @param string $name nokta ile birleşitirilmiş session indexi (index1.index2)
     * @param $value
     * @return bool
     */
    public function set(string $name, $value): bool
    {
        if ($this->status()) {
            dot_aray_set($_SESSION, $name, $value);
            return true;
        }
        return false;
    }


    /**
     * Adı girilen oturum verisini döndürür. Bulamazsa false döner.
     *
     * @param string $name nokta ile birleşitirilmiş session indexi (index1.index2)
     * @return mixed
     */
    public function get(string $name)
    {
        if ($this->status()) {
            return dot_aray_get($_SESSION, $name);
        }
        return false;
    }


    /**
     * Adı girilen oturum verisini siler.
     * @param string $name nokta ile birleşitirilmiş session indexi (index1.index2)
     * @return bool
     */
    public function remove(string $name): bool
    {
        if ($this->status()) {
            return dot_array_del($_SESSION, $name);
        }
        return false;
    }


    /**
     * Session::set methodu ile benzer çalışır, farkı verinin bir kez taşınıp otomatik silinmesidir.
     * Session::set gibi dizi derinlik özelliği yoktur.
     *
     * @param string $name oluşturulan oturum verisinin adı
     * @param $value
     * @param int $lifecycle
     * @return bool
     */
    public function tempSet(string $name, $value, int $lifecycle = 1): bool
    {
        if ($this->status()) {
            $_SESSION['__TEMPRORY__']['__TIMES__'][$name] = 0;
            $_SESSION['__TEMPRORY__']['__LIFECYCLE__'][$name] = $lifecycle;
            $_SESSION['__TEMPRORY__'][$name] = $value;
            return true;
        }
        return false;
    }

    /**
     * Session::get methodu ile benzer çalışır, farkı sadece Session::tmpSet ile oluşturulan verileri erişmesidir.
     *
     * @param string $name
     * @return mixed
     */
    public function tempGet(string $name)
    {
        return $_SESSION['__TEMPRORY__'][$name] ?? null;
    }


    /**
     * Session::tmpSet medhoduyla oluşturulan verileri bir sonraki sayfada siler.
     */
    public function tempClear()
    {
        if (isset($_SESSION['__TEMPRORY__'])) {

            foreach ($_SESSION['__TEMPRORY__']['__TIMES__'] as $key => $val) {

                if ($val == $_SESSION['__TEMPRORY__']['__LIFECYCLE__'][$key]) {
                    unset($_SESSION['__TEMPRORY__']['__LIFECYCLE__'][$key]);
                    unset($_SESSION['__TEMPRORY__']['__TIMES__'][$key]);
                    unset($_SESSION['__TEMPRORY__'][$key]);
                } else {
                    $_SESSION['__TEMPRORY__']['__TIMES__'][$key]++;
                }
            }
        }
    }

    /**
     * @return bool
     */
    public function status(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * Tüm oturum bilgilerini siler.
     */
    public function destroy(): bool
    {
        if ($this->status()) {
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            return session_destroy();
        }

        return false;
    }
}
