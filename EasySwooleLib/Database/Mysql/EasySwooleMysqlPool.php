<?php

namespace EasySwooleLib\Database\Mysql;

use EasySwoole\Component\Singleton;
use EasySwoole\ORM\Db\Config;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\Db\MysqliClient;
use EasySwoole\ORM\DbManager;
use Swoole\Coroutine\Scheduler;
use Swoole\Timer;

class EasySwooleMysqlPool
{
    use Singleton;

    public function checkPool(string $name, array $config): bool
    {
        $success = false;
        $error = '';
        $scheduler = new Scheduler();
        $scheduler->add(function () use ($config, &$success, &$error) {
            $configObject = new \EasySwoole\Mysqli\Config($config);
            $client = new MysqliClient($configObject);
            $ret = $client->connect();
            if ($ret) {
                $success = true;
            } else {
                $error = "connection fail, error: " . $client->mysqlClient()->connect_error;
            }
        });
        $scheduler->start();
        Timer::clearAll();

        if ($success) {
            return true;
        } else {
            throw new \Error("EasySwoole MySQL Pool [{$name}] configuration error, error: " . $error);
        }
    }

    public function initPool(string $name, array $config): void
    {
        $this->checkPool($name, $config);
        $configObj = new Config($config);
        $connection = new Connection($configObj);
        DbManager::getInstance()->addConnection($connection, $name);

        $onQuery = \config('databases.onQuery');

        if (!empty($onQuery) && is_callable($onQuery)) {
            DbManager::getInstance()->onQuery($onQuery);
        }
    }

    public function keepMin(string $name, array $config)
    {
        $num = null;

        if (!empty($config['minObjectNum'])) {
            $num = $config['minObjectNum'];
        }

        DbManager::getInstance()->getConnection($name)->__getClientPool()->keepMin($num);
    }
}
