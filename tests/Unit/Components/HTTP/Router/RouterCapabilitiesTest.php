<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\Router;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteCollection;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteMethod;
use Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Avax\Components\HTTP\Router\System\Flows\MatchRoute\MatchRoute;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

final class RouterCapabilitiesTest extends TestCase
{
    public function test_match_route_finds_static_route() : void
    {
        $collection = new RouteCollection();
        $route      = new RouteDefinition(method: new RouteMethod('GET'), uri: '/hello', action: 'HelloAction');
        $collection->add($route);

        $request = $this->createMock(RequestInterface::class);
        $request->method('getMethod')->willReturn('GET');

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/hello');
        $request->method('getUri')->willReturn($uri);

        $matcher = new MatchRoute();
        $match   = $matcher->execute($collection, $request);

        $this->assertNotNull($match);
        $this->assertSame('HelloAction', $match->action());
    }
}
