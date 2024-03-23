<?php

namespace App\Crontab;

use EasySwoole\Crontab\JobInterface;

class FooCrontab implements JobInterface
{
    public function jobName(): string
    {
        return 'foo';
    }

    public function crontabRule(): string
    {
        return '* * * * *';
    }

    public function run()
    {
        go(function () {
            echo date('Y-m-d H:i:s') . " Welcome to use EasySwoole Framework ^_^ [Crontab]\n";
        });
    }

    public function onException(\Throwable $throwable)
    {
        throw $throwable;
    }
}
