<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Tests\Unit;

use Avax\HTTP\Request\Request;
use Avax\HTTP\Router\Matching\RouteMatcherRegistry;
use Avax\HTTP\Router\Routing\Exceptions\InvalidRouteException;
use Avax\HTTP\Router\Routing\Exceptions\RouteNotFoundException;
use Avax\HTTP\Router\Routing\HttpRequestRouter;
use Avax\HTTP\Router\Validation\RouteConstraintValidator;
use Avax\HTTP\URI\UriBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use RuntimeException;

final class HttpRouterRoutingTest extends TestCase
{
    private HttpRequestRouter $router;

    /**
     * @throws \Avax\HTTP\Router\Validation\Exceptions\InvalidConstraintException
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    public function test_optional_segment_with_value_is_captured() : void
    {
        $this->router->registerRoute(
            method: 'GET',
            path  : '/users/{id?}',
            action: 'handler'
        );

        $request = new Request(
            serverParams: ['REQUEST_METHOD' => 'GET'],
            uri         : UriBuilder::createFromString(uri: 'https://example.com/users/99')
        );

        $route = $this->router->resolve(request: $request);

        $this->assertSame(expected: '99', actual: $route->parameters['id']);
    }

    /**
     * @throws \Avax\HTTP\Router\Validation\Exceptions\InvalidConstraintException
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    public function test_optional_segment_is_accepted() : void
    {
        $this->router->registerRoute(
            method  : 'GET',
            path    : '/users/{id?}',
            action  : 'handler',
            defaults: ['id' => '42']
        );

        $request = new Request(
            serverParams: ['REQUEST_METHOD' => 'GET'],
            uri         : UriBuilder::createFromString(uri: 'https://example.com/users')
        );

        $route = $this->router->resolve(request: $request);

        $this->assertSame(expected: 'GET', actual: $route->method);
        $this->assertSame(expected: '/users/{id?}', actual: $route->path);
        $this->assertArrayNotHasKey(key: 'id', array: $route->parameters);
        $this->assertArrayHasKey(key: 'id', array: $route->defaults);
    }

    /**
     * @throws \Avax\HTTP\Router\Validation\Exceptions\InvalidConstraintException
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    public function test_optional_segment_without_value_falls_back_to_defaults() : void
    {
        $this->router->registerRoute(
            method  : 'GET',
            path    : '/accounts/{accountId?}',
            action  : 'handler',
            defaults: ['accountId' => 'default-account']
        );

        $request = new Request(
            serverParams: ['REQUEST_METHOD' => 'GET'],
            uri         : UriBuilder::createFromString(uri: 'https://example.com/accounts')
        );

        $route = $this->router->resolve(request: $request);

        $this->assertSame(expected: 'default-account', actual: $route->defaults['accountId']);
        $this->assertArrayNotHasKey(key: 'accountId', array: $route->parameters);
    }

    /**
     * @throws \Avax\HTTP\Router\Validation\Exceptions\InvalidConstraintException
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    public function test_wildcard_segment_captures_remainder() : void
    {
        $this->router->registerRoute(
            method: 'GET',
            path  : '/files/{path*}',
            action: 'handler'
        );

        $request = new Request(
            serverParams: ['REQUEST_METHOD' => 'GET'],
            uri         : UriBuilder::createFromString(uri: 'https://example.com/files/a/b/c')
        );

        $route = $this->router->resolve(request: $request);

        $this->assertSame(expected: 'a/b/c', actual: $route->parameters['path']);
    }

    /**
     * @throws \Avax\HTTP\Router\Validation\Exceptions\InvalidConstraintException
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    public function test_wildcard_segment_captures_single_segment_too() : void
    {
        $this->router->registerRoute(
            method: 'GET',
            path  : '/assets/{path*}',
            action: 'handler'
        );

        $request = new Request(
            serverParams: ['REQUEST_METHOD' => 'GET'],
            uri         : UriBuilder::createFromString(uri: 'https://example.com/assets/logo.png')
        );

        $route = $this->router->resolve(request: $request);

        $this->assertSame(expected: 'logo.png', actual: $route->parameters['path']);
    }

    /**
     * @throws \Avax\HTTP\Router\Validation\Exceptions\InvalidConstraintException
     */
    public function test_fallback_route_is_returned_when_no_match() : void
    {
        $this->router->fallback(handler: 'fallback-handler');

        $request = new Request(
            serverParams: ['REQUEST_METHOD' => 'GET'],
            uri         : UriBuilder::createFromString(uri: 'https://example.com/missing')
        );

        $route = $this->router->resolve(request: $request);

        $this->assertSame(expected: '__router.fallback', actual: $route->name);
        $this->assertSame(expected: 'fallback-handler', actual: $route->action);
    }

    /**
     * @throws \Avax\HTTP\Router\Validation\Exceptions\InvalidConstraintException
     */
    public function test_fallback_handles_different_http_method() : void
    {
        $this->router->fallback(handler: 'fallback-handler');

        $request = new Request(
            serverParams: ['REQUEST_METHOD' => 'POST'],
            uri         : UriBuilder::createFromString(uri: 'https://example.com/unknown')
        );

        $route = $this->router->resolve(request: $request);

        $this->assertSame(expected: '__router.fallback', actual: $route->name);
        $this->assertSame(expected: 'fallback-handler', actual: $route->action);
    }

    /**
     * @throws \Avax\HTTP\Router\Validation\Exceptions\InvalidConstraintException
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    public function test_domain_route_matches_only_when_host_matches() : void
    {
        $this->router->registerRoute(
            method: 'GET',
            path  : '/home',
            action: 'handler',
            domain: 'api.example.com'
        );

        $requestMatch = new Request(
            serverParams: ['REQUEST_METHOD' => 'GET'],
            uri         : UriBuilder::createFromString(uri: 'https://api.example.com/home')
        );

        $matched = $this->router->resolve(request: $requestMatch);
        $this->assertSame(expected: 'handler', actual: $matched->action);

        $requestMiss = new Request(
            serverParams: ['REQUEST_METHOD' => 'GET'],
            uri         : UriBuilder::createFromString(uri: 'https://www.example.com/home')
        );

        $this->expectException(exception: RouteNotFoundException::class);
        $this->router->resolve(request: $requestMiss);
    }

    public function test_wildcard_must_be_final_segment() : void
    {
        $this->expectException(exception: InvalidRouteException::class);

        $this->router->registerRoute(
            method: 'GET',
            path  : '/files/{path*}/extra',
            action: 'handler'
        );
    }

    public function test_only_one_wildcard_allowed() : void
    {
        $this->expectException(exception: InvalidRouteException::class);

        $this->router->registerRoute(
            method: 'GET',
            path  : '/first/{a*}/second/{b*}',
            action: 'handler'
        );
    }

    public function test_wildcard_must_be_named() : void
    {
        $this->expectException(exception: InvalidRouteException::class);

        $this->router->registerRoute(
            method: 'GET',
            path  : '/{*}',
            action: 'handler'
        );
    }

    /**
     * @throws \Avax\HTTP\Router\Validation\Exceptions\InvalidConstraintException
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    public function test_constraint_failure_throws() : void
    {
        $this->router->registerRoute(
            method     : 'GET',
            path       : '/users/{id}',
            action     : 'handler',
            constraints: ['id' => '\\d+']
        );

        $request = new Request(
            serverParams: ['REQUEST_METHOD' => 'GET'],
            uri         : UriBuilder::createFromString(uri: 'https://example.com/users/abc')
        );

        $this->expectException(exception: RuntimeException::class);
        $this->router->resolve(request: $request);
    }

    public function test_named_route_lookup() : void
    {
        $this->router->registerRoute(
            method: 'GET',
            path  : '/named',
            action: 'handler',
            name  : 'named.route'
        );

        $found = $this->router->getByName(name: 'named.route');

        $this->assertSame(expected: '/named', actual: $found->path);
        $this->assertTrue(condition: $this->router->hasNamedRoute(name: 'named.route'));
    }

    public function test_prefix_is_applied_and_cleared() : void
    {
        $this->router->setPrefix('/api');
        $this->router->registerRoute(
            method: 'GET',
            path  : '/ping',
            action: 'handler'
        );
        $this->router->clearPrefix();
        $this->router->registerRoute(
            method: 'GET',
            path  : '/raw',
            action: 'handler2'
        );

        $routes = $this->router->allRoutes();

        $paths = array_map(static fn($route) => $route->path, $routes['GET'] ?? []);

        $this->assertContains(needle: '/api/ping', haystack: $paths);
        $this->assertContains(needle: '/raw', haystack: $paths);
    }

    public function test_invalid_path_throws_exception() : void
    {
        $this->expectException(exception: InvalidRouteException::class);
        $this->router->registerRoute(
            method: 'GET',
            path  : 'missing-slash',
            action: 'handler'
        );
    }

    protected function setUp() : void
    {
        $matcherRegistry = RouteMatcherRegistry::withDefaults(logger: new NullLogger);
        $matcher         = $matcherRegistry->get(key: 'domain');

        $this->router = new HttpRequestRouter(
            constraintValidator: new RouteConstraintValidator,
            matcher            : $matcher,
            logger             : new NullLogger
        );
    }
}