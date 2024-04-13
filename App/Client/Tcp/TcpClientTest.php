<?php

namespace App\Client\Tcp;

use Swoole\Coroutine;
use Swoole\Coroutine\Client;
use function Swoole\Coroutine\run;

class TcpClientTest
{
    public function tcpSend()
    {
        $client = new Client(SWOOLE_TCP);
        $client->set([
            'open_length_check'     => true,
            'package_max_length'    => 81920,
            'package_length_type'   => 'N',
            'package_length_offset' => 0,
            'package_body_offset'   => 4,
        ]);

        if (!$client->connect('127.0.0.1', 9504)) {
            echo 'tcp connect fail!\n';
        }

        $sendBody = json_encode([
            'controller' => 'Index',
            'action'     => 'index'
        ]);
        $client->send(pack('N', strlen($sendBody)) . $sendBody);
        $recv = $client->recv();

        if ($recv) {
            $len = unpack('N', $recv)[1];
            $recvData = substr($recv, 4, $len);

            echo "tcpSend Result: \n";
            var_dump($recvData);

            return $recvData;
        }

        return null;
    }

    public function test()
    {
        if (Coroutine::getCid() > 0) {
            // 协程环境下
            return $this->tcpSend();
        } else {
            // 非协程环境下
            run(function () {
                $this->tcpSend();
            });
        }

        return null;
    }
}

//$test = new TcpClientTest();
//$test->test();
