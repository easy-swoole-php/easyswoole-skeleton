<?php

namespace EasySwooleLib\Router;

use EasySwoole\Component\Singleton;
use FastRoute\RouteCollector;
use function config;

class RouterManager
{
    use Singleton;

    public function registerRoute(RouteCollector &$routeCollector)
    {
        $routers = config('router');

        foreach ($routers as $router) {

            if (!class_exists($router)) {
                continue;
            }

            $routeObj = new $router;

            if (!$routeObj instanceof IRouterInterface) {
                continue;
            }

            $routeObj->register($routeCollector);
        }
    }
}
