<?php

namespace App\Client\Http;

use EasySwoole\HttpClient\HttpClient;
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

if (!class_exists(HttpClient::class)) {
    require_once __DIR__ . '/../../../vendor/autoload.php';
}

class HttpClientTest
{
    private function httpGetRequest()
    {
        $host = 'http://httpbin.org';
        $client = new HttpClient($host);
        $resp = $client->get();
        $result = $resp->getBody();

        echo "\nhttpGetRequest Result: \n";
        echo $result . "\n";

        return $result;
    }

    public function testGetRequest()
    {
        if (Coroutine::getCid() > 0) {
            // 协程环境下
            return $this->httpGetRequest();
        } else {
            // 非协程环境下
            run(function () {
                $this->httpGetRequest();
            });
        }

        return null;
    }

    private function httpPostRequest(string $url)
    {
        $client = new HttpClient($url);
        $resp = $client->post();
        $result = $resp->getBody();

        echo "\nhttpPostRequest Result: \n";
        echo $result . "\n";

        return $result;
    }

    public function testPostRequest()
    {
        $result = [];

        if (Coroutine::getCid() > 0) {
            // 协程环境下
            $result[] = $this->httpPostRequest("http://localhost:9501/hello/XueSi");
            $result[] = $this->httpPostRequest("http://localhost:9503/hello/XueSi");
            return $result;
        } else {
            // 非协程环境下
            run(function () {
                $this->httpPostRequest("http://localhost:9501/hello/XueSi");
                $this->httpPostRequest("http://localhost:9503/hello/XueSi");
            });
        }

        return null;
    }
}

//$test = new HttpClientTest();
//$test->testGetRequest();
//$test->testPostRequest();
