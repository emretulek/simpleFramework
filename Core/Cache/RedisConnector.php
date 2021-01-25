<?php

namespace Core\Cache;

use Exception;
use Redis;

class RedisConnector
{

    /**
     * @param string $server
     * @param int $port
     * @param array $options
     * @return Redis
     * @throws Exception
     */
    public function connect(string $server, int $port = 6379, array $options = []): Redis
    {
        if (!extension_loaded('redis')) {
            throw new Exception("redis eklentisi kurulu değil.");
        }

        $redis = new Redis();
        $redis->connect($server, $port);

        if (isset($options['auth']['pass'])) {
            $redis->auth($options['auth']);
        }

        if (isset($options['database'])) {
            $redis->select((int)$options['database']);
        }

        if (!empty($options['prefix'])) {
            $redis->setOption(Redis::OPT_PREFIX, $options['prefix']);
        }

        if (!empty($options['read_timeout'])) {
            $redis->setOption(Redis::OPT_READ_TIMEOUT, $options['read_timeout']);
        }

        if (!empty($options['scan'])) {
            $redis->setOption(Redis::OPT_SCAN, $options['scan']);
        }

        if (!empty($options['name'])) {
            $redis->client('SETNAME', $options['name']);
        }

        return $redis;
    }
}
