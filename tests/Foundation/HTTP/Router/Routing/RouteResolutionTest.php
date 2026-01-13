<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Tests\Unit;

use Avax\HTTP\Request\Request;
use Avax\HTTP\Response\Classes\Response;
use Avax\HTTP\Response\Classes\Stream;
use Avax\HTTP\Router\Routing\RouteDefinition;
use Avax\HTTP\Router\Routing\RouteMatcher;
use Avax\HTTP\URI\UriBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Test suite for route matching and compilation.
 */
final class RouteResolutionTest extends TestCase
{
    private RouteMatcher $matcher;

    /**
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    public function test_compile_optional_parameter() : void
    {
        $routes = ['GET' => ['/users/{id?}' => new RouteDefinition(
            method       : 'GET',
            path         : '/users/{id?}',
            action       : static fn() => new Response(stream: Stream::fromString(content: ''), protocolVersion: 200),
            middleware   : [],
            name         : 'test',
            constraints  : [],
            defaults     : [],
            domain       : null,
            attributes   : [],
            authorization: null
        )
        ]
        ];

        $request = new Request(serverParams: [], uri: UriBuilder::createFromString(uri: 'http://example.com/users/123'));
        $result  = $this->matcher->match(routes: $routes, request: $request);

        $this->assertNotNull(actual: $result);
        [$route, $matches] = $result;
        $this->assertEquals(expected: '/users/{id?}', actual: $route->path);
        $this->assertEquals(expected: '123', actual: $matches['id']);
    }

    /**
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    public function test_compile_wildcard_parameter() : void
    {
        $routes = ['GET' => ['/files/{path*}' => new RouteDefinition(
            method       : 'GET',
            path         : '/files/{path*}',
            action       : static fn() => new Response(stream: Stream::fromString(content: ''), protocolVersion: 200),
            middleware   : [],
            name         : 'test',
            constraints  : [],
            defaults     : [],
            domain       : null,
            attributes   : [],
            authorization: null
        )
        ]
        ];

        $request = new Request(serverParams: [], uri: UriBuilder::createFromString(uri: 'http://example.com/files/a/b/c'));
        $result  = $this->matcher->match(routes: $routes, request: $request);

        $this->assertNotNull(actual: $result);
        [$route, $matches] = $result;
        $this->assertEquals(expected: '/files/{path*}', actual: $route->path);
        $this->assertEquals(expected: 'a/b/c', actual: $matches['path']);
    }

    protected function setUp() : void
    {
        $this->matcher = new RouteMatcher(logger: new NullLogger);
    }
}