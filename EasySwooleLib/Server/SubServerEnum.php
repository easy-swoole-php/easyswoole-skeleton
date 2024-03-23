<?php

namespace EasySwooleLib\Server;

class SubServerEnum
{
    public const SUPPORT_SUB_SERVER_TYPE = [
        EASYSWOOLE_WEB_SOCKET_SERVER => [
            EASYSWOOLE_WEB_SOCKET_SERVER,
            EASYSWOOLE_WEB_SERVER,
            EASYSWOOLE_TCP_SERVER,
            EASYSWOOLE_UDP_SERVER,
        ],
        EASYSWOOLE_WEB_SERVER        => [
            EASYSWOOLE_WEB_SERVER,
            EASYSWOOLE_TCP_SERVER,
            EASYSWOOLE_UDP_SERVER,
        ],
        EASYSWOOLE_TCP_SERVER        => [
            EASYSWOOLE_TCP_SERVER,
            EASYSWOOLE_UDP_SERVER,
        ],
        EASYSWOOLE_UDP_SERVER        => [
            EASYSWOOLE_UDP_SERVER,
            EASYSWOOLE_TCP_SERVER,
        ]
    ];

    public const EASYSWOOLE_SERVER_TYPE_TO_SWOOLE_SOCK_TYPE = [
        EASYSWOOLE_WEB_SOCKET_SERVER => SWOOLE_TCP,
        EASYSWOOLE_WEB_SERVER        => SWOOLE_TCP,
        EASYSWOOLE_TCP_SERVER        => SWOOLE_TCP,
        EASYSWOOLE_UDP_SERVER        => SWOOLE_UDP,
    ];

    public const EASYSWOOLE_SERVER_TYPE_TO_NAME = [
        EASYSWOOLE_WEB_SOCKET_SERVER => 'EASYSWOOLE_WEB_SOCKET_SERVER',
        EASYSWOOLE_WEB_SERVER        => 'EASYSWOOLE_WEB_SERVER',
        EASYSWOOLE_TCP_SERVER        => 'EASYSWOOLE_TCP_SERVER',
        EASYSWOOLE_UDP_SERVER        => 'EASYSWOOLE_UDP_SERVER',
    ];

    public static function getSupportSubServerTypes(int $mainServerType): array
    {
        return self::SUPPORT_SUB_SERVER_TYPE[$mainServerType] ?? [];
    }

    public static function getSwooleSockType(int $serverType): int
    {
        return self::EASYSWOOLE_SERVER_TYPE_TO_SWOOLE_SOCK_TYPE[$serverType] ?? 0;
    }

    public static function getServerName(int $serverType): string
    {
        return self::EASYSWOOLE_SERVER_TYPE_TO_NAME[$serverType] ?? '';
    }
}
