<?php

namespace Core\Connector;

use Memcached;
use RuntimeException;

class MemcachedConnector
{
    protected Memcached $memcached;

    /**
     * Memcached connection
     * @param array $servers
     * @param null $connectionId
     * @param array $options
     * @param array $sasl
     * @return Memcached
     */
    public function connect(array $servers, $connectionId = null, array $options = [], array $sasl = []): Memcached
    {
        if (!extension_loaded('memcached')) {
            throw new RuntimeException('memcached eklentisi kurulu değil.');
        }

        $this->memcached = new Memcached($connectionId);

        //set username password
        if (count($sasl) === 2) {
            $this->memcached->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
            $this->memcached->setSaslAuthData($sasl['username'], $sasl['password']);
        }

        //var olan bağlantı kullanılmıyorsa server ekle
        if (!$this->memcached->getServerList()) {
            foreach ($servers as $server) {
                $this->memcached->addServer(
                    $server['host'], $server['port'], $server['weight']
                );
            }
        }

        //varsa ayarlar
        if ($options) {
            $this->memcached->setOptions($options);
        }

        return $this->memcached;
    }
}
