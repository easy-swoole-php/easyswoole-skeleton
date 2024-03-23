<?php

namespace EasySwooleLib\Server\WebSocket\WebSocketController;

use Swoole\Server as SwooleServer;
use Throwable;
use EasySwoole\Socket\Client\WebSocket;
use EasySwoole\Socket\Bean\Response;

class ExceptionHandler
{
    public static function handle(SwooleServer $server, Throwable $throwable, string $raw, WebSocket $client, Response $response)
    {
        $response->setMessage('System Error!');
        $response->setStatus($response::STATUS_RESPONSE_AND_CLOSE);
    }
}
