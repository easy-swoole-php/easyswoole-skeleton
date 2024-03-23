<?php

namespace App\Router;

use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwooleLib\Router\IRouterInterface;
use FastRoute\RouteCollector;

class ApiRouter implements IRouterInterface
{
    public function register(RouteCollector &$routeCollector): void
    {
        $routeCollector->addRoute('GET', '/hello/{id:\d+}', '/Index/hello');

        $routeCollector->addRoute('POST', '/hello/{name}', '/Index/hello');

        $routeCollector->addRoute('GET', '/json', function (Request $request, Response $response) {
            return json(['foo' => 'bar', 'text' => 'This is json.']);
        });

        $routeCollector->addRoute('GET', '/text', function (Request $request, Response $response) {
            return response('This is simple text.');
        });
    }
}
