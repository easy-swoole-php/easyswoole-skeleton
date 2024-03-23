<?php

namespace EasySwooleLib\Router;

use FastRoute\RouteCollector;

interface IRouterInterface
{
    public function register(RouteCollector &$routeCollector): void;
}
