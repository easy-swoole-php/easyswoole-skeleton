<?php

namespace EasySwooleLib\Redis;

use EasySwoole\Component\Singleton;
use EasySwoole\Redis\Config as RedisConfig;
use EasySwoole\Redis\Redis;
use EasySwoole\RedisPool\RedisPool;
use Swoole\Coroutine\Scheduler;
use Swoole\Timer;

class EasySwooleRedisPool
{
    use Singleton;

    public function checkPool(string $name, array $config)
    {
        $success   = false;
        $error     = '';
        $scheduler = new Scheduler();
        $scheduler->add(function () use ($config, &$success, &$error) {
            $redisConfigObj = new RedisConfig($config);
            $client         = new Redis($redisConfigObj);
            $ret            = $client->connect();
            if ($ret) {
                $success = true;
            } else {
                $error = "connection fail.";
            }
        });
        $scheduler->start();
        Timer::clearAll();

        if ($success) {
            return true;
        } else {
            throw new \Exception("EasySwoole Redis Pool [{$name}] configuration error: " . $error);
        }
    }

    public function initPool(string $name, array $config)
    {
        $this->checkPool($name, $config);
        $redisConfigObj = new RedisConfig($config);
        RedisPool::getInstance()->register($redisConfigObj, $name);
    }
}
