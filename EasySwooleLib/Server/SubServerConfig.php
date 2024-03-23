<?php

namespace EasySwooleLib\Server;

use EasySwoole\Component\Singleton;

class SubServerConfig
{
    use Singleton;

    protected $serverRegisters = [];

    public function addSubServerRegister(string $serverName, array $serverRegister)
    {
        $this->serverRegisters[$serverName] = $serverRegister;
    }

    public function getSubServerRegister(string $serverName)
    {
        return $this->serverRegisters[$serverName] ?? [];
    }
}
