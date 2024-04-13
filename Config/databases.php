<?php
return [
    'default' => [
        'enable'           => false,
        'driver'           => 'mysql',
        'host'             => '127.0.0.1', // 数据库地址
        // 'host'             => 'mysql', // 数据库地址
        'port'             => 3306, // 数据库端口
        'user'             => 'root', // 数据库用户名
        'password'         => 'easyswoole', // 数据库用户密码
        'timeout'          => 45, // 数据库连接超时时间
        'charset'          => 'utf8mb4', // 数据库字符编码
        'database'         => 'mysql', // 数据库名
        'useMysqli'        => true,
        'strict_type'      => false, // 不开启严格模式
        'fetch_mode'       => false,
        'returnCollection' => false, // 设置返回结果为 数组
        'pool'             => [
            'autoPing'          => 5, // 自动 ping 客户端链接的间隔
            'intervalCheckTime' => 15 * 1000, // 设置 连接池定时器执行频率
            'maxIdleTime'       => 10, // 设置 连接池对象最大闲置时间 (秒)
            'maxObjectNum'      => 20, // 设置 连接池最大数量
            'minObjectNum'      => 5, // 设置 连接池最小数量
            'getObjectTimeout'  => 3.0, // 设置 获取连接池的超时时间
            'loadAverageTime'   => 0.001, // 设置 负载阈值
        ],
        'enable_keep_min'  => false, // 是否开启链接预热
    ],
    'onQuery' => null,
];
