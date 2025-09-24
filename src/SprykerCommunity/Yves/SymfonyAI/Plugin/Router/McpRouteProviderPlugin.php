<?php

namespace SprykerCommunity\Yves\SymfonyAI\Plugin\Router;

use Spryker\Yves\Router\Plugin\RouteProvider\AbstractRouteProviderPlugin;
use Spryker\Yves\Router\Route\RouteCollection;

class McpRouteProviderPlugin extends AbstractRouteProviderPlugin
{
    public const ROUTE_MCP = 'mcp-server';

    public function addRoutes(RouteCollection $routeCollection): RouteCollection
    {
        $route = $this->buildRoute('/mcp', 'SymfonyAI', 'Server', 'index');
        $route->setMethods(['POST']);

        // If you have CSRF on for POSTs in Yves, disable it for this API route:
        // $route->setOption('_csrf', false);

        $routeCollection->add(static::ROUTE_MCP, $route);
        return $routeCollection;
    }
}
