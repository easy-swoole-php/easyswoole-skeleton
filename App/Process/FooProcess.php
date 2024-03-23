<?php

namespace App\Process;

use EasySwoole\Component\Process\AbstractProcess;
use Throwable;

class FooProcess extends AbstractProcess
{
    protected function run($arg)
    {
        go(function () {
            while (true) {
                echo date('Y-m-d H:i:s') . " Welcome to use EasySwoole API Framework ^_^ [Process]\n";
                \Co::sleep(60);
            }
        });
    }

    protected function onException(Throwable $throwable, ...$args)
    {
        parent::onException($throwable, $args);
    }
}
