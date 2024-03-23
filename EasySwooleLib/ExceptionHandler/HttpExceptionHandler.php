<?php

namespace EasySwooleLib\ExceptionHandler;

use EasySwoole\EasySwoole\Trigger;
use EasySwoole\Http\Message\Status;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwooleLib\AppMode;
use Throwable;

class HttpExceptionHandler
{
    public static function handle(Throwable $throwable, Request $request, Response $response)
    {
        $response->withStatus(Status::CODE_INTERNAL_SERVER_ERROR);

        if (AppMode::isDevMode() || AppMode::isTestMode()) {
            $response->write(nl2br($throwable->getMessage() . "\n" . $throwable->getTraceAsString()));
        } else {
            $response->write('System Error');
        }

        Trigger::getInstance()->throwable($throwable);
    }
}
