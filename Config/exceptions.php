<?php
return [
    'handler_callable' => [
        'http'                            => [\EasySwooleLib\ExceptionHandler\HttpExceptionHandler::class, 'handle'],
        'tcp_controller_dispatcher'       => [\EasySwooleLib\Server\Tcp\TcpController\ExceptionHandler::class, 'handle'],
        'udp_controller_dispatcher'       => [\EasySwooleLib\Server\Udp\UdpController\ExceptionHandler::class, 'handle'],
        'websocket_controller_dispatcher' => [\EasySwooleLib\Server\WebSocket\WebSocketController\ExceptionHandler::class, 'handle'],
    ]
];
