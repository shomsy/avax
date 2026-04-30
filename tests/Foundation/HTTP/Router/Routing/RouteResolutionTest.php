<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\Router\Routing;

use Avax\Components\HTTP\Request\Request;
use Avax\Components\HTTP\Response\Response;
use Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Avax\Components\HTTP\Router\System\Flows\ResolveRequest\Matching\RouteMatcher;
use Avax\Components\HTTP\Router\System\Foundation\Exceptions\ReservedRouteNameException;
use Avax\Components\HTTP\URI\UriBuilder;
use Avax\Tests\TestCase;
use Override;
use Psr\Log\NullLogger;
use ReflectionException;

/**
 * Test suite for route matching and compilation.
 */
final class RouteResolutionTest extends TestCase
{
    private RouteMatcher $matcher;

    /**
     * @throws ReservedRouteNameException
     * @throws ReflectionException
     */
    public function test_compile_optional_parameter() : void
    {
        $routes = ['GET' => ['/users/{id?}' => new RouteDefinition(
            method       : 'GET',
            path         : '/users/{id?}',
            action       : static fn () => Response::text(content: ''),
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

        $request = new Request(serverParams: [], uri: UriBuilder::createFromString(uri: 'https://example.com/users/123'));
        $result  = $this->matcher->match(routes: $routes, request: $request);

        $this->assertNotNull(actual: $result);
        [$route, $matches] = $result;
        $this->assertEquals(expected: '/users/{id?}', actual: $route->path);
        $this->assertEquals(expected: '123', actual: $matches['id']);
    }

    /**
     * @throws ReservedRouteNameException
     * @throws ReflectionException
     */
    public function test_compile_wildcard_parameter() : void
    {
        $routes = ['GET' => ['/files/{path*}' => new RouteDefinition(
            method       : 'GET',
            path         : '/files/{path*}',
            action       : static fn () => Response::text(content: ''),
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

        $request = new Request(serverParams: [], uri: UriBuilder::createFromString(uri: 'https://example.com/files/a/b/c'));
        $result  = $this->matcher->match(routes: $routes, request: $request);

        $this->assertNotNull(actual: $result);
        [$route, $matches] = $result;
        $this->assertEquals(expected: '/files/{path*}', actual: $route->path);
        $this->assertEquals(expected: 'a/b/c', actual: $matches['path']);
    }

    #[Override]
    protected function setUp() : void
    {
        $this->matcher = new RouteMatcher(logger: new NullLogger);
    }
}
