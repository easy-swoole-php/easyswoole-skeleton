<?php

namespace EasySwooleLib\Server\WebSocket;

use EasySwooleLib\Server\Utility\WebSocketServerUtil;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as SwooleWebsocketServer;
use Swoole\Server as SwooleServer;

class WebSocketServer
{
    public static function onRequest(SwooleRequest $request, SwooleResponse $response)
    {
        echo "[SubServer][WebSocket][onRequest]Client: Request.\n";
    }

    // 监听WebSocket连接打开事件
    public static function onOpen(SwooleWebsocketServer $server, SwooleRequest $request)
    {
        echo "[SubServer][WebSocket][onOpen]Client: Open.\n";

        $server->push($request->fd, "[SubServer][WebSocket][onOpen]hello, welcome\n");
    }

    // 监听WebSocket消息事件
    public static function onMessage(SwooleWebsocketServer $server, Frame $frame)
    {
        echo "[SubServer][WebSocket][onMessage]Client: Message.\n";

        echo "Message: {$frame->data}\n";
        $server->push($frame->fd, "server: {$frame->data}");

        WebSocketServerUtil::sendToAll($frame->data);
    }

    // 监听WebSocket连接关闭事件
    public static function onClose(SwooleServer $server, int $fd, int $reactorId)
    {
        echo "[SubServer][WebSocket][onClose]Client: Close.\n";

        echo "client-{$fd} is closed\n";
    }
}
