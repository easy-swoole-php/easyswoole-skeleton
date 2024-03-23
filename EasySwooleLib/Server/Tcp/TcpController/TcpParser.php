<?php

namespace EasySwooleLib\Server\Tcp\TcpController;

use EasySwoole\Socket\AbstractInterface\ParserInterface;
use EasySwoole\Socket\Bean\Caller;
use EasySwoole\Socket\Bean\Response;

class TcpParser implements ParserInterface
{
    public function decode($raw, $client): ?Caller
    {
        [$header, $data] = Protocol::getInstance()->unpackData($raw);
        $data = json_decode($data, true);
        $caller = new Caller();
        $controller = !empty($data['controller']) ? $data['controller'] : 'Index';
        $action = !empty($data['action']) ? $data['action'] : 'index';
        $param = !empty($data['param']) ? $data['param'] : [];
        $controller = "App\\TcpController\\{$controller}";
        $caller->setControllerClass($controller);
        $caller->setAction($action);
        $caller->setArgs($param);
        return $caller;
    }

    public function encode(Response $response, $client): ?string
    {
        return Protocol::getInstance()->packBody($response->getMessage());
    }
}
