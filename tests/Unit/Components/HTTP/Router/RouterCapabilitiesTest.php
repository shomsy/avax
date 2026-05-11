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
        $route      = new RouteDefinition(method: RouteMethod::GET, uri: '/hello', action: 'HelloAction');
        $collection->add($route);

        $request = $this->createMock(RequestInterface::class);
        $request->method('getMethod')->willReturn('GET');

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/hello');
        $request->method('getUri')->willReturn($uri);

        $matcher = new MatchRoute();
        $match   = $matcher->execute($collection, $request);

        self::assertTrue($match->isMatch());
        self::assertNotNull($match->route);
        self::assertSame('HelloAction', $match->route->action());
    }

    public function test_match_route_returns_not_found_for_unknown_path() : void
    {
        $collection = new RouteCollection();
        $route      = new RouteDefinition(method: RouteMethod::GET, uri: '/hello', action: 'HelloAction');
        $collection->add($route);

        $request = $this->createMock(RequestInterface::class);
        $request->method('getMethod')->willReturn('GET');

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/unknown');
        $request->method('getUri')->willReturn($uri);

        $matcher = new MatchRoute();
        $match   = $matcher->execute($collection, $request);

        self::assertTrue($match->isNotFound());
        self::assertFalse($match->isMatch());
        self::assertFalse($match->isMethodNotAllowed());
    }

    public function test_match_route_detects_method_not_allowed() : void
    {
        $collection = new RouteCollection();
        $route      = new RouteDefinition(method: RouteMethod::GET, uri: '/users', action: 'UsersAction');
        $collection->add($route);

        $request = $this->createMock(RequestInterface::class);
        $request->method('getMethod')->willReturn('POST');

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/users');
        $request->method('getUri')->willReturn($uri);

        $matcher = new MatchRoute();
        $match   = $matcher->execute($collection, $request);

        self::assertTrue($match->isMethodNotAllowed());
        self::assertContains(RouteMethod::GET, $match->allowedMethods);
    }

    public function test_match_route_extracts_parameters() : void
    {
        $collection = new RouteCollection();
        $route      = new RouteDefinition(method: RouteMethod::GET, uri: '/users/{id}', action: 'UserAction');
        $collection->add($route);

        $request = $this->createMock(RequestInterface::class);
        $request->method('getMethod')->willReturn('GET');

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/users/42');
        $request->method('getUri')->willReturn($uri);

        $matcher = new MatchRoute();
        $match   = $matcher->execute($collection, $request);

        self::assertTrue($match->isMatch());
        self::assertSame(['id' => '42'], $match->parameters);
    }
}
