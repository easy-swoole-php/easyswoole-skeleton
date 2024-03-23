<?php

namespace EasySwooleLib\Cli;

use EasySwoole\Command\Caller;
use EasySwoole\Command\CommandManager;
use EasySwoole\Component\Singleton;

class ArgvArgcParser
{
    use Singleton;

    /**
     * @var int
     */
    private $originArgc;

    /**
     * @var array
     */
    private $originArgv = [];

    /**
     * @var Caller
     */
    private $caller;

    /**
     * @var CommandManager
     */
    private $commandManager;

    public function init(int $originArgc, array $originArgv)
    {
        $this->originArgc = $originArgc;
        $this->originArgv = $originArgv;

        $caller = new Caller();
        $caller->setScript(current($originArgv));
        $command = next($originArgv);
        if (!$command) {
            $command = "";
        }
        $caller->setCommand($command);
        $caller->setParams($originArgv);
        reset($originArgv);
        $this->caller = $caller;

        $commandManager = new CommandManager();
        $commandManager->run($caller);
        $this->commandManager = $commandManager;

        return $this;
    }

    public function getOriginArgc(): int
    {
        return $this->originArgc;
    }

    public function getOriginArgv(): array
    {
        return $this->originArgv;
    }

    public function getCaller(): Caller
    {
        return $this->caller;
    }

    public function getCommandManager(): CommandManager
    {
        return $this->commandManager;
    }
}
