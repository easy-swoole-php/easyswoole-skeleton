<?php

namespace App\HttpController;

use App\Client\Http\HttpClientTest;
use App\Client\Tcp\TcpClientTest;
use App\Client\Udp\UdpClientTest;
use App\Client\WebSocket\WebSocketClientTest;
use EasySwooleLib\Controller\BaseController;

class Client extends BaseController
{
    // eg: curl "http://localhost:9501/Client/testTcpClient"
    // eg: curl "http://localhost:9503/Client/testTcpClient"
    public function testTcpClient()
    {
        $tcpClientTest = new TcpClientTest();
        $result = $tcpClientTest->test();
        return json(['result' => $result]);
    }

    // eg: curl "http://localhost:9501/Client/testUdpClient"
    public function testUdpClient()
    {
        $udpClientTest = new UdpClientTest();
        $result = $udpClientTest->test();
        return json(['result' => $result]);
    }

    // eg: curl "http://localhost:9501/Client/testHttpClient"
    public function testHttpClient()
    {
        $httpClientTest = new HttpClientTest();
        $getResult = $httpClientTest->testGetRequest();
        $postResult = $httpClientTest->testPostRequest();
        return json([
            'getResult'  => $getResult,
            'postResult' => $postResult
        ]);
    }

    // eg: curl "http://localhost:9501/Client/testWebSocketClient" 需主服务为 WebSocket 时可测试
    // eg: curl "http://localhost:9502/Client/testWebSocketClient" 需子服务为 WebSocket 时可测试
    public function testWebSocketClient()
    {
        $webSocketClientTest = new WebSocketClientTest();
        $result = $webSocketClientTest->test();
        return json(['result' => $result]);
    }
}
