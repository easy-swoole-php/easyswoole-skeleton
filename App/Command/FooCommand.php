<?php

namespace App\Command;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\ORM\Db\MysqliClient;
use function Swoole\Coroutine\run;

class FooCommand implements CommandInterface
{
    public function commandName(): string
    {
        return 'foo';
    }

    public function exec(): ?string
    {
        run(function () {
            $config = config('databases.default');
            var_dump($config);
            $configObject = new \EasySwoole\Mysqli\Config($config);
            $client = new MysqliClient($configObject);
            $ret = $client->connect();
            var_dump($ret);
            var_dump($client->mysqlClient()->error);
            var_dump($client->mysqlClient()->errno);
        });

        return null;
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        return $commandHelp;
    }

    public function desc(): string
    {
        return $this->commandName();
    }
}
