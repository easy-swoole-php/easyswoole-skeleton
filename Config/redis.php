<?php
return [
    'default' => [
        'enable'           => false,
        'host'             => '127.0.0.1', // Redis 地址
        // 'host'             => 'redis', // Redis 地址
        'port'             => 6379, // Redis 端口
        'auth'             => '', // Redis 密码
        'timeout'          => 3.0, // Redis 操作超时时间
        'reconnectTimes'   => 3, // Redis 自动重连次数
        'db'               => 0, // Redis 库
        'serialize'        => \EasySwoole\Redis\Config::SERIALIZE_NONE, // 序列化类型，默认不序列化
        'packageMaxLength' => 1024 * 1024 * 2, // 允许操作的最大数据包 2M
        'prefix'           => '', // key 前缀
        'debug'            => true, // 调试模式，开启时会打印 redis 操作执行的 command
        'pool'             => [
            'intervalCheckTime' => 15 * 1000, // 设置 连接池定时器执行频率
            'maxIdleTime'       => 10, // 设置 连接池对象最大闲置时间 (秒)
            'maxObjectNum'      => 20, // 设置 连接池最大数量
            'minObjectNum'      => 5, // 设置 连接池最小数量
            'getObjectTimeout'  => 3.0, // 设置 获取连接池的超时时间
            'loadAverageTime'   => 0.001, // 设置 负载阈值
        ],
    ]
];
