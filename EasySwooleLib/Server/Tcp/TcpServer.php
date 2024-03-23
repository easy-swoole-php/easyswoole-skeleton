<?php

namespace EasySwooleLib\Server\Tcp;

use EasySwooleLib\Server\Tcp\TcpController\Protocol;
use EasySwooleLib\Server\Utility\TcpServerUtil;
use Swoole\Server as SwooleServer;

class TcpServer
{
    // 监听连接进入事件
    public static function onConnect(SwooleServer $server, int $fd, int $reactorId)
    {
        echo "[SubServer][Tcp][onConnect]Client: Connect.\n";
    }

    // 监听数据接收事件
    public static function onReceive(SwooleServer $server, int $fd, int $reactorId, string $data)
    {
        echo "[SubServer][Tcp][onReceive]Server: Receive.\n";

        $protocol = Protocol::getInstance(false, 'tcp');
        [$header, $data] = $protocol->unpackData($data);
        $body = "Server: {$data}";
        $sendData = $protocol->packBody($body);
        $server->send($fd, $sendData);

        TcpServerUtil::sendToAll($sendData);
    }

    // 监听连接关闭事件
    public static function onClose(SwooleServer $server, int $fd, int $reactorId)
    {
        echo "[SubServer][Tcp][onClose]Client: Close.\n";
    }
}
