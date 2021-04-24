<?php


namespace Core\Session;


use Core\App;
use Core\Cache\MemcachedCache;
use Core\Cache\RedisCache;
use Core\Connector\MemcachedConnector;
use Core\Connector\RedisConnector;
use RuntimeException;
use SessionHandlerInterface;

class SessionManager
{

    private App $app;
    private array $sessionConfig;
    private ?string $sessionDriver;

    /**
     * config/session.php
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->sessionConfig = $app->config['session'];
        $this->sessionDriver = $app->config['session']['driver'];
    }


    /**
     * Ön tanımlı driver seçimi
     * @return SessionHandlerInterface
     */
    public function getDefaultDriver():SessionHandlerInterface
    {
        if ($this->sessionDriver) {

            if (!method_exists($this, $selectDriver = $this->sessionDriver . 'Driver')) {
                throw new RuntimeException("$this->sessionDriver bu oturum yönetimi desteklenmiyor.");
            }

            return $this->$selectDriver();
        }

        return $this->nullDriver();
    }


    /**
     * Php default session handler
     * @return SessionHandlerInterface
     */
    protected function nullDriver(): SessionHandlerInterface
    {
        return new NullSessionHandler();
    }


    /**
     * @return SessionHandlerInterface
     */
    protected function fileDriver(): SessionHandlerInterface
    {
        return new FileSessionHandler($this->sessionConfig);
    }


    /**
     * @return SessionHandlerInterface
     */
    protected function DatabaseDriver(): SessionHandlerInterface
    {
        return new DatabaseSessionHandler($this->app, $this->sessionConfig);
    }


    /**
     * @return SessionHandlerInterface
     */
    protected function MemcachedDriver(): SessionHandlerInterface
    {
        $memcachedDriver = $this->app->config['memcached'][$this->sessionConfig['memcached']];
        return new MemcachedSessionHandler(
            new MemcachedCache(new MemcachedConnector(), $memcachedDriver),
            $this->sessionConfig
        );
    }


    /**
     * @return SessionHandlerInterface
     */
    protected function RedisDriver(): SessionHandlerInterface
    {
        $redisDriver = $this->app->config['redis'][$this->sessionConfig['redis']];
        return new RedisSessionHandler(
            new RedisCache(new RedisConnector(), $redisDriver),
            $this->sessionConfig
        );
    }
}
