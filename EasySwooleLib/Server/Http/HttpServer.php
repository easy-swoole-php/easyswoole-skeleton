<?php

namespace EasySwooleLib\Server\Http;

use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

class HttpServer
{
    public static function onRequest(SwooleRequest $request, SwooleResponse $response)
    {
        echo "[SubServer][Http][onRequest]Client: Request.\n";

        $response->header('Content-Type', 'text/html; charset=utf-8');
        $response->end('[SubServer][Http][onRequest]<h1>Hello EasySwoole. #' . rand(1000, 9999) . '</h1>');
    }
}
