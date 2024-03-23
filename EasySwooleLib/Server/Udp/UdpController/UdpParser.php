<?php

namespace EasySwooleLib\Server\Udp\UdpController;

use EasySwoole\Socket\AbstractInterface\ParserInterface;
use EasySwoole\Socket\Bean\Caller;
use EasySwoole\Socket\Bean\Response;

class UdpParser implements ParserInterface
{
    public function decode($raw, $client): ?Caller
    {
        $data = json_decode($raw, true);
        $caller = new Caller();
        $controller = !empty($data['controller']) ? $data['controller'] : 'Index';
        $action = !empty($data['action']) ? $data['action'] : 'index';
        $param = !empty($data['param']) ? $data['param'] : [];
        $controller = "App\\UdpController\\{$controller}";
        $caller->setControllerClass($controller);
        $caller->setAction($action);
        $caller->setArgs($param);
        return $caller;
    }

    public function encode(Response $response, $client): ?string
    {
        return json_encode($response->getMessage());
    }
}
