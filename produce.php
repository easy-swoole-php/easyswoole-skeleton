<?php

use EasySwoole\Log\LoggerInterface;
use EasySwoole\EasySwoole\Swoole\EventRegister;

return [
    'SERVER_NAME' => "EasySwoole",
    'MAIN_SERVER' => [
        'LISTEN_ADDRESS' => '0.0.0.0',
        'PORT'           => 9501,
        'SERVER_TYPE'    => EASYSWOOLE_WEB_SERVER, // 可选为 EASYSWOOLE_SERVER  EASYSWOOLE_WEB_SERVER EASYSWOOLE_WEB_SOCKET_SERVER
        'SOCK_TYPE'      => SWOOLE_TCP,
        'RUN_MODEL'      => SWOOLE_PROCESS,
        'SETTING'        => [
            'worker_num'    => swoole_cpu_num(),
            'reload_async'  => true,
            'max_wait_time' => 3,
            // (可选参数）使用 http 上传大文件时可以进行配置
            // 'package_max_length' => 100 * 1024 * 1024, // 即 100 M
            // (可选参数) 允许处理静态文件 html 等，详细请看 http://swoole.easyswoole.com/ServerStart/Http/serverSetting.html
            // 'document_root' => '/easyswoole/public',
            // 'enable_static_handler' => true,
            // 主服务需使用 tcp 控制器时相关配置
            // 'open_length_check'     => true,
            // 'package_max_length'    => 81920,
            // 'package_length_type'   => 'N',
            // 'package_length_offset' => 0,
            // 'package_body_offset'   => 4,
        ],
        'TASK'           => [
            // 需使用task时开启
            'workerNum' => 0,
            // 'workerNum'     => 4,
            // 'maxRunningNum' => 128,
            // 'timeout'       => 15,
        ],
        'CALLBACKS'      => [
            EventRegister::onWorkerStart => [\App\Server\Http\HttpServer::class, 'onWorkerStart'],
            // EventRegister::onHandShake => [\EasySwooleLib\Server\WebSocket\WebSocketEvent::class, 'onHandShake'],
            // EventRegister::onReceive => [\App\Server\Tcp\TcpServer::class, 'onReceive']
        ],
    ],
    "LOG"         => [
        'dir'            => null,
        'level'          => LoggerInterface::LOG_LEVEL_DEBUG,
        'handler'        => new \EasySwooleLib\Logger\LoggerHandler(),
        'logConsole'     => true,
        'displayConsole' => true,
        'ignoreCategory' => []
    ],
    'TEMP_DIR'    => null,
];
