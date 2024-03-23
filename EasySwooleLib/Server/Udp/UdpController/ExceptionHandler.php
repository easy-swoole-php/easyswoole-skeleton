<?php

namespace EasySwooleLib\Server\Udp\UdpController;

use EasySwoole\Socket\Client\Udp;
use Swoole\Server as SwooleServer;
use Throwable;
use EasySwoole\Socket\Bean\Response;

class ExceptionHandler
{
    public static function handle(SwooleServer $server, Throwable $throwable, string $raw, Udp $client, Response $response)
    {
        $response->setMessage('System Error!');
        $response->setStatus($response::STATUS_RESPONSE_AND_CLOSE);
    }
}
