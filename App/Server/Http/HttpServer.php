<?php

namespace App\Server\Http;

use EasySwooleLib\Database\Mysql\EasySwooleMysqlPool;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Swoole\Server as SwooleServer;
use function config;

class HttpServer
{
    public static function onWorkerStart(SwooleServer $server, int $workerId)
    {
        // mysql pool 链接预热
        $databases = config('databases');

        foreach ($databases as $name => $config) {
            $enable = $config['enable'] ?? false;
            $enableKeepMin = $config['enable_keep_min'] ?? false;

            if (!$enable) {
                continue;
            }

            if (!$enableKeepMin) {
                continue;
            }

            $poolConfig = $config['pool'] ?? [];

            if ($poolConfig) {
                $config = array_merge($config, $poolConfig);
            }

            if (!empty($config['driver'])) {
                if ($config['driver'] === 'mysql') {
                    EasySwooleMysqlPool::getInstance()->keepMin($name, $config);
                }
            }
        }
    }

    public static function onRequest(SwooleRequest $request, SwooleResponse $response)
    {
        echo "[SubServer][Http][onRequest]Client: Request.\n";

        $response->header('Content-Type', 'text/html; charset=utf-8');
        $response->end('[SubServer][Http][onRequest]<h1>Hello EasySwoole. #' . rand(1000, 9999) . '</h1>');
    }
}
