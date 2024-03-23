<?php

namespace EasySwoole\EasySwoole;

use EasySwoole\Component\Di;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwooleLib\Event\GlobalEvent;
use EasySwooleLib\Http\CrossOriginManager;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');
        GlobalEvent::initialize();

        Di::getInstance()->set(SysConst::HTTP_GLOBAL_ON_REQUEST, function (Request $request, Response $response): bool {
            GlobalEvent::initHttpGlobalOnRequest($request, $response);
            $isOk = CrossOriginManager::handle($request, $response);

            if (!$isOk) {
                return false;
            }

            return true;
        });

        Di::getInstance()->set(SysConst::HTTP_GLOBAL_AFTER_REQUEST, function (Request $request, Response $response): void {

        });
    }

    public static function mainServerCreate(EventRegister $register)
    {
        GlobalEvent::mainServerCreate($register);
    }
}
