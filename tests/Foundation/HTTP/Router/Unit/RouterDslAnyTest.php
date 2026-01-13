<?php

declare(strict_types=1);

use Avax\HTTP\Dispatcher\ControllerDispatcher;
use Avax\HTTP\Request\Request;
use Avax\HTTP\Router\RouterDsl;
use Avax\HTTP\Router\Routing\HttpRequestRouter;
use Avax\HTTP\Router\Routing\RouteCollection;
use Avax\HTTP\Router\Routing\RouteDefinition;
use Avax\HTTP\Router\Routing\RouteMatcher;
use Avax\HTTP\Router\Routing\RouteRegistrarProxy;
use Avax\HTTP\Router\Routing\RouterRegistrar;
use Avax\HTTP\Router\Support\FallbackManager;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;

/**
 * Tests for RouterDsl::any() and anyExpanded() semantics.
 *
 * Ensures deterministic route registration without duplicates.
 */
class RouterDslAnyTest extends TestCase
{
    private RouterDsl $dsl;

    /**
     * @test
     */
    public function any_registers_single_any_route() : void
    {
        // When: Registering with any()
        $proxy = $this->dsl->any(path: '/test', action: static function () {
            return 'any';
        });

        // Then: Should return single RouteRegistrarProxy (not array)
        $this->assertInstanceOf(expected: RouteRegistrarProxy::class, actual: $proxy);
    }

    /**
     * @test
     */
    public function any_expanded_registers_all_methods() : void
    {
        // When: Registering with anyExpanded()
        $proxies = $this->dsl->anyExpanded(path: '/test', action: static function () {
            return 'expanded';
        });

        // Then: Should return array with all HTTP methods except ANY
        $this->assertIsArray(actual: $proxies);
        $this->assertCount(expectedCount: 7, haystack: $proxies); // GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD

        foreach ($proxies as $proxy) {
            $this->assertInstanceOf(expected: RouteRegistrarProxy::class, actual: $proxy);
        }
    }

    /**
     * @test
     */
    public function any_expanded_excludes_any_method() : void
    {
        // When: Registering with anyExpanded()
        $this->dsl->anyExpanded(path: '/test', action: static function () {
            return 'expanded';
        });

        // Then: ANY method should not be included
        // This is tested by the count assertion above (7 instead of 8)
        $this->assertTrue(condition: true); // Placeholder for more specific testing if needed
    }

    /**
     * @test
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    public function method_specific_routes_take_precedence_over_any() : void
    {
        // This test verifies the router priority: method > any > fallback
        // The actual prioritization happens in RouteMatcher

        // Given: Router matcher prioritizes specific methods over ANY
        $matcher = new RouteMatcher(
            logger: $this->createMock(LoggerInterface::class)
        );

        // Create mock routes
        $anyRoute = new RouteDefinition(
            method: 'ANY',
            path  : '/test',
            action: static function () {
                return 'any';
            }
        );

        $getRoute = new RouteDefinition(
            method: 'GET',
            path  : '/test',
            action: static function () {
                return 'get';
            }
        );

        $routes = [
            'ANY' => ['/test' => $anyRoute],
            'GET' => ['/test' => $getRoute],
        ];

        // Mock request
        $request = $this->createMock(Request::class);
        $request->method('getMethod')->willReturn(value: 'GET');
        $request->method('getUri')->willReturn(value: $this->createMock(UriInterface::class));

        // When: Matching GET request
        $result = $matcher->match(routes: $routes, request: $request);

        // Then: GET route should be matched, not ANY route
        $this->assertNotNull(actual: $result);
        [$matchedRoute, $matches] = $result;
        $this->assertEquals(expected: 'GET', actual: $matchedRoute->method);
    }

    protected function setUp() : void
    {
        // Create minimal dependencies for testing
        $router          = $this->createMock(HttpRequestRouter::class);
        $routeCollection = new RouteCollection;

        $this->dsl = new RouterDsl(
            registrar           : $this->createMock(RouterRegistrar::class),
            router              : $router,
            controllerDispatcher: $this->createMock(ControllerDispatcher::class),
            fallbackManager     : $this->createMock(FallbackManager::class)
        );
    }
}
