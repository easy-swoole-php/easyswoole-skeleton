<?php

namespace EasySwooleLib\Redis\Utility;

use EasySwoole\Redis\Redis;
use EasySwoole\RedisPool\RedisPool;

trait ConnectTrait
{
    /**
     * echo
     * Echo the given string
     *
     * @param string   $message
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return string Returns message
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/echo
     */
    public static function echo(string $message, int $dbIndex = null, string $connectionName = self::DEFAULT_CONNECT)
    {
        $result = RedisPool::invoke(function (Redis $redis) use ($message, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->echo($message);
        }, $connectionName);

        // handle exception
        $command = "ECHO {$message}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * ping
     * Check the current connection status
     *
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return bool|string TRUE if the command is successful or returns message
     * Throws a RedisException object on connectivity error, as described above.
     *
     * @throws Exception\RedisException
     * @link https://redis.io/commands/ping
     */
    public static function ping(int $dbIndex = null, string $connectionName = self::DEFAULT_CONNECT)
    {
        $result = RedisPool::invoke(function (Redis $redis) use ($dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->ping();
        }, $connectionName);

        // handle exception
        $command = "PING";
        self::handleException($command, $connectionName, $result);

        return $result;
    }
}
