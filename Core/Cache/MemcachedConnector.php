<?php

namespace Core\Cache;

use Memcached;
use RuntimeException;

class MemcachedConnector
{
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

        $memcached = new Memcached($connectionId);

        //set username password
        if (count($sasl) === 2) {
            $memcached->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
            $memcached->setSaslAuthData($sasl['username'], $sasl['password']);
        }

        //var olan bağlantı kullanılmıyorsa server ekle
        if (!$memcached->getServerList()) {
            foreach ($servers as $server) {
                $memcached->addServer(
                    $server['host'], $server['port'], $server['weight']
                );
            }
        }

        //varsa ayarlar
        if ($options) {
            $memcached->setOptions($options);
        }

        return $memcached;
    }
}
