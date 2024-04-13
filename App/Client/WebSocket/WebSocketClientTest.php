<?php

namespace App\Client\WebSocket;

use Swoole\Coroutine;
use EasySwoole\HttpClient\HttpClient;
use function Swoole\Coroutine\run;

if (!class_exists(HttpClient::class)) {
    require_once __DIR__ . '/../../../vendor/autoload.php';
}

class WebSocketClientTest
{
    public function websocketPush()
    {
        $client = new HttpClient('http://127.0.0.1:9501');
        $upgradeResult = $client->upgrade(true);

        if ($upgradeResult) {
            $sendBody = json_encode([
                'controller' => 'Index',
                'action'     => 'index'
            ]);
            $client->push($sendBody);
            $recv = $client->recv();

            echo "websocketPush Result: \n";
            var_dump($recv);

            return $recv;
        }

        return null;
    }

    public function test()
    {
        if (Coroutine::getCid() > 0) {
            // 协程环境下
            return $this->websocketPush();
        } else {
            // 非协程环境下
            run(function () {
                $this->websocketPush();
            });
        }

        return null;
    }
}

//$test = new WebSocketClientTest();
//$test->test();
