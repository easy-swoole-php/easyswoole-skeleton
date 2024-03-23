<?php

namespace EasySwooleLib\Crontab;

use EasySwoole\Crontab\Config;
use EasySwoole\EasySwoole\Crontab\Crontab;
use RuntimeException;
use Throwable;
use function config;

class EasySwooleCrontabManager
{
    public static function registerCrontab()
    {
        $crontabConfig = config('crontab');
        $enable = $crontabConfig['enable'] ?? false;
        $workerNum = $crontabConfig['worker_num'] ?? 3;

        if (!$enable) {
            return;
        }

        $crontabConfigObj = new Config();
        $crontabConfigObj->setWorkerNum($workerNum);
        $onException = [self::class, 'onException'];

        if (!empty($crontabConfig['on_exception']) && is_callable($crontabConfig['on_exception'])) {
            $onException = $crontabConfig['on_exception'];
        }

        $crontabConfigObj->setOnException($onException);
        $crontabObject = Crontab::getInstance($crontabConfigObj);
        $crontabClasses = $crontabConfig['crontab'];

        foreach ($crontabClasses as $crontabClass) {
            if (!class_exists($crontabClass)) {
                throw new RuntimeException($crontabClass . ' class not exist.');
            }

            $crontabObject->register(new $crontabClass);
        }
    }

    public static function onException(Throwable $throwable)
    {
        throw $throwable;
    }
}
