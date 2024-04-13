<?php

namespace App\Client\Udp;

use Swoole\Coroutine;
use Swoole\Coroutine\Client;
use function Swoole\Coroutine\run;

class UdpClientTest
{
    public function udpSendTo()
    {
        $client = new Client(SWOOLE_UDP);
        $sendBody = json_encode([
            'controller' => 'Index',
            'action'     => 'index'
        ]);
        $client->sendto('127.0.0.1', 9505, $sendBody);
        $recv = $client->recv();

        echo "udpSendTo Result: \n";
        var_dump($recv);

        return $recv;
    }

    public function test()
    {
        if (Coroutine::getCid() > 0) {
            // 协程环境下
            return $this->udpSendTo();
        } else {
            // 非协程环境下
            run(function () {
                $this->udpSendTo();
            });
        }

        return null;
    }
}

//$test = new UdpClientTest();
//$test->test();
