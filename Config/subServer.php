<?php

use EasySwoole\EasySwoole\Swoole\EventRegister;

return [
    [
        'enable'         => false,
        'name'           => 'websocket',
        'server_type'    => EASYSWOOLE_WEB_SOCKET_SERVER, // 可选为 EASYSWOOLE_WEB_SOCKET_SERVER EASYSWOOLE_WEB_SERVER EASYSWOOLE_TCP_SERVER EASYSWOOLE_UDP_SERVER
        // 1、当主服务为 EASYSWOOLE_WEB_SOCKET_SERVER 类型时，子服务可为 EASYSWOOLE_WEB_SOCKET_SERVER 或 EASYSWOOLE_WEB_SERVER 或 EASYSWOOLE_TCP_SERVER 或 EASYSWOOLE_UDP_SERVER
        // 2、当主服务为 EASYSWOOLE_WEB_SERVER 类型时，子服务可为 EASYSWOOLE_WEB_SERVER 或 EASYSWOOLE_TCP_SERVER 或 EASYSWOOLE_UDP_SERVER
        // 3、当主服务为 EASYSWOOLE_SERVER 类型时，子服务仅可为 EASYSWOOLE_TCP_SERVER 或 EASYSWOOLE_UDP_SERVER，根据主服务的 SOCK_TYPE 进一步确定
        'listen_address' => '0.0.0.0',
        'port'           => 9502,
        'callbacks'      => [
//            EventRegister::onRequest => [\App\Server\WebSocket\WebSocketServer::class, 'onRequest'],
//            EventRegister::onOpen    => [\App\Server\WebSocket\WebSocketServer::class, 'onOpen'],
//            EventRegister::onMessage => [\App\Server\WebSocket\WebSocketServer::class, 'onMessage'],
//            EventRegister::onClose   => [\App\Server\WebSocket\WebSocketServer::class, 'onClose'],
        ],
        'setting'        => [],
    ],
    [
        'enable'         => false,
        'name'           => 'http',
        'server_type'    => EASYSWOOLE_WEB_SERVER,
        'listen_address' => '0.0.0.0',
        'port'           => 9503,
        'callbacks'      => [
//            EventRegister::onRequest => [\App\Server\Http\HttpServer::class, 'onRequest'],
        ],
        'setting'        => [],
    ],
    [
        'enable'         => false,
        'name'           => 'tcp',
        'server_type'    => EASYSWOOLE_TCP_SERVER,
        'listen_address' => '0.0.0.0',
        'port'           => 9504,
        'callbacks'      => [
//            EventRegister::onConnect => [\App\Server\Tcp\TcpServer::class, 'onConnect'],
//            EventRegister::onReceive => [\App\Server\Tcp\TcpServer::class, 'onReceive'],
//            EventRegister::onClose   => [\App\Server\Tcp\TcpServer::class, 'onClose'],
        ],
        'setting'        => [
            'open_length_check'     => true,
            'package_max_length'    => 81920,
            'package_length_type'   => 'N',
            'package_length_offset' => 0,
            'package_body_offset'   => 4,
        ],
    ],
    [
        'enable'         => false,
        'name'           => 'udp',
        'server_type'    => EASYSWOOLE_UDP_SERVER,
        'listen_address' => '0.0.0.0',
        'port'           => 9505,
        'callbacks'      => [
//            EventRegister::onPacket => [\App\Server\Udp\UdpServer::class, 'onPacket'],
        ],
        'setting'        => [],
    ],
];
