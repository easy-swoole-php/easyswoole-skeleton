<?php

namespace App\Process\Queue\Consumer;

use App\Queue\FooQueue;
use EasySwoole\Component\Process\AbstractProcess;
use Throwable;

class FooQueueConsumerProcess extends AbstractProcess
{
    protected function run($arg)
    {
        go(function () {
            FooQueue::getInstance()->consumeOrdinaryJob();
        });
    }

    protected function onException(Throwable $throwable, ...$args)
    {
        parent::onException($throwable, $args);
    }
}
