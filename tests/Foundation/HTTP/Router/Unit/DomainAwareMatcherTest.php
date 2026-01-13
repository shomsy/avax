<?php

declare(strict_types=1);

use Avax\HTTP\Router\Routing\DomainAwareMatcher;
use Avax\HTTP\Router\Routing\RouteDefinition;
use Avax\HTTP\Router\Routing\RouteMatcher;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Tests for DomainAwareMatcher - multi-tenant domain routing.
 */
class DomainAwareMatcherTest extends TestCase
{
    private DomainAwareMatcher $matcher;

    private RouteMatcher $baseMatcher;

    /**
     * @test
     */
    public function matches_routes_without_domain_constraints() : void
    {
        $route = new RouteDefinition(
            method: 'GET',
            path  : '/api/users',
            action: 'UserController@index',
            domain: null // No domain constraint
        );

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(
            value: $this->createMock(UriInterface::class)
        );

        // Base matcher should be called and return a match
        $this->baseMatcher->expects(invocationRule: $this->once())
            ->method(constraint: 'matches')
            ->with($route, $request)
            ->willReturn(value: true);

        $this->assertTrue(condition: $this->matcher->matches(route: $route, request: $request));
    }

    /**
     * @test
     */
    public function matches_exact_domain_constraints() : void
    {
        $route = new RouteDefinition(
            method: 'GET',
            path  : '/admin',
            action: 'AdminController@index',
            domain: 'admin.example.com'
        );

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getHost')->willReturn(value: 'admin.example.com');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(value: $uri);

        $this->baseMatcher->expects(invocationRule: $this->once())
            ->method(constraint: 'matches')
            ->with($route, $request)
            ->willReturn(value: true);

        $this->assertTrue(condition: $this->matcher->matches(route: $route, request: $request));
    }

    /**
     * @test
     */
    public function rejects_wrong_domain() : void
    {
        $route = new RouteDefinition(
            method: 'GET',
            path  : '/admin',
            action: 'AdminController@index',
            domain: 'admin.example.com'
        );

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getHost')->willReturn(value: 'api.example.com');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(value: $uri);

        // Base matcher should not be called since domain doesn't match
        $this->baseMatcher->expects(invocationRule: $this->never())
            ->method(constraint: 'matches');

        $this->assertFalse(condition: $this->matcher->matches(route: $route, request: $request));
    }

    /**
     * @test
     */
    public function supports_wildcard_subdomains() : void
    {
        $route = new RouteDefinition(
            method: 'GET',
            path  : '/dashboard',
            action: 'DashboardController@index',
            domain: '*.example.com' // Wildcard subdomain
        );

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getHost')->willReturn(value: 'tenant1.example.com');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(value: $uri);

        $this->baseMatcher->expects(invocationRule: $this->once())
            ->method(constraint: 'matches')
            ->with($route, $request)
            ->willReturn(value: true);

        $this->assertTrue(condition: $this->matcher->matches(route: $route, request: $request));
    }

    /**
     * @test
     */
    public function rejects_wildcard_subdomain_mismatch() : void
    {
        $route = new RouteDefinition(
            method: 'GET',
            path  : '/dashboard',
            action: 'DashboardController@index',
            domain: '*.example.com'
        );

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getHost')->willReturn(value: 'tenant1.other.com');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(value: $uri);

        $this->baseMatcher->expects(invocationRule: $this->never())
            ->method(constraint: 'matches');

        $this->assertFalse(condition: $this->matcher->matches(route: $route, request: $request));
    }

    /**
     * @test
     */
    public function handles_port_numbers_in_host() : void
    {
        $route = new RouteDefinition(
            method: 'GET',
            path  : '/api',
            action: 'ApiController@index',
            domain: 'api.example.com'
        );

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getHost')->willReturn(value: 'api.example.com:8080');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(value: $uri);

        $this->baseMatcher->expects(invocationRule: $this->once())
            ->method(constraint: 'matches')
            ->with($route, $request)
            ->willReturn(value: true);

        $this->assertTrue(condition: $this->matcher->matches(route: $route, request: $request));
    }

    /**
     * @test
     */
    public function converts_host_to_lowercase() : void
    {
        $route = new RouteDefinition(
            method: 'GET',
            path  : '/test',
            action: 'TestController@index',
            domain: 'example.com'
        );

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getHost')->willReturn(value: 'EXAMPLE.COM');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(value: $uri);

        $this->baseMatcher->expects(invocationRule: $this->once())
            ->method(constraint: 'matches')
            ->with($route, $request)
            ->willReturn(value: true);

        $this->assertTrue(condition: $this->matcher->matches(route: $route, request: $request));
    }

    /**
     * @test
     */
    public function match_method_finds_best_domain_match() : void
    {
        $routes = [
            'GET' => [
                '/api'      => new RouteDefinition(
                    method: 'GET',
                    path  : '/api',
                    action: 'ApiController@index',
                    domain: 'api.example.com'
                ),
                '/fallback' => new RouteDefinition(
                    method: 'GET',
                    path  : '/fallback',
                    action: 'FallbackController@index',
                    domain: null // No domain constraint
                ),
            ],
        ];

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getHost')->willReturn(value: 'api.example.com');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn(value: 'GET');
        $request->method('getUri')->willReturn(value: $uri);

        // Should find the domain-specific route first
        $this->baseMatcher->expects(invocationRule: $this->once())
            ->method(constraint: 'match')
            ->with($routes, $request)
            ->willReturn(value: null); // Simulate no initial match

        // Then check individual routes for domain matching
        $this->baseMatcher->expects(invocationRule: $this->exactly(count: 2))
            ->method(constraint: 'matches')
            ->willReturnCallback(callback: static function ($route) {
                return $route->path === '/api'; // Only API route matches
            });

        $result = $this->matcher->match(routes: $routes, request: $request);

        $this->assertNotNull(actual: $result);
        $this->assertEquals(expected: '/api', actual: $result[0]->path);
    }

    protected function setUp() : void
    {
        $this->baseMatcher = $this->createMock(RouteMatcher::class);
        $this->matcher     = new DomainAwareMatcher(baseMatcher: $this->baseMatcher);
    }
}
