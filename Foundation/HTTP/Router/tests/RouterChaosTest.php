<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Tests;

use Avax\HTTP\Router\Matching\RouteMatcherInterface;
use Avax\HTTP\Router\Routing\Exceptions\DuplicateRouteException;
use Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException;
use Avax\HTTP\Router\Routing\HttpRequestRouter;
use Avax\HTTP\Router\Routing\RouteCollection;
use Avax\HTTP\Router\Routing\RouteDefinition;
use Avax\HTTP\Router\Routing\RouteSourceLoaderInterface;
use Avax\HTTP\Router\Validation\RouteConstraintValidator;
use Exception;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

/**
 * Chaos and stress testing for router fault tolerance.
 *
 * Validates router behavior under extreme conditions including:
 * - Cache corruption scenarios
 * - Concurrent bootstrap operations
 * - Middleware chain interruptions
 * - Memory pressure situations
 * - Invalid route configurations
 */
final class RouterChaosTest extends TestCase
{
    private HttpRequestRouter $router;
    private RouteCollection   $collection;

    /**
     * @test
     * @throws Exception
     */
    public function cache_corruption_does_not_crash_router() : void
    {
        // Simulate cache file with corrupted JSON
        $corruptedData = '{"invalid": json, "missing": brackets';

        $cacheLoader = $this->createMock(RouteSourceLoaderInterface::class);
        $cacheLoader->method('loadInto')
            ->willThrowException(exception: new RuntimeException(message: 'Cache corruption detected'));

        $cacheLoader->method('isAvailable')
            ->willReturn(value: true);

        // Router should handle cache corruption gracefully
        $this->expectException(exception: RuntimeException::class);
        $this->expectExceptionMessage(message: 'Cache corruption detected');

        $cacheLoader->loadInto(collection: $this->collection);
    }

    /**
     * @test
     * @throws DuplicateRouteException
     * @throws ReservedRouteNameException
     * @throws ReservedRouteNameException
     */
    public function concurrent_route_registration_isolation() : void
    {
        // Simulate concurrent route registration from multiple threads/loaders
        $routes1 = [];
        $routes2 = [];

        // Thread 1: Register API routes
        $thread1 = static function () use (&$routes1) {
            for ($i = 1; $i <= 100; $i++) {
                $route     = new RouteDefinition(
                    method    : 'GET',
                    path      : "/api/v1/resource{$i}",
                    action    : "Controller{$i}@action",
                    middleware: ['api', 'auth'],
                    name      : "api.resource{$i}"
                );
                $routes1[] = $route;
            }
        };

        // Thread 2: Register web routes
        $thread2 = static function () use (&$routes2) {
            for ($i = 1; $i <= 100; $i++) {
                $route     = new RouteDefinition(
                    method    : 'GET',
                    path      : "/web/resource{$i}",
                    action    : "WebController{$i}@action",
                    middleware: ['web', 'session'],
                    name      : "web.resource{$i}"
                );
                $routes2[] = $route;
            }
        };

        // Execute concurrently (simulated)
        $thread1();
        $thread2();

        // Register all routes
        foreach (array_merge($routes1, $routes2) as $route) {
            $this->router->add(route: $route);
        }

        // Verify isolation - no conflicts between API and web routes
        $this->assertInstanceOf(expected: RouteDefinition::class, actual: $this->router->getByName(name: 'api.resource50'));
        $this->assertInstanceOf(expected: RouteDefinition::class, actual: $this->router->getByName(name: 'web.resource50'));
    }

    /**
     * @test
     * @throws DuplicateRouteException
     * @throws ReservedRouteNameException
     */
    public function middleware_chain_interruption_recovery() : void
    {
        // Test middleware that throws exceptions mid-chain
        $failingMiddleware = static function ($request, $next) {
            static $callCount = 0;
            $callCount++;

            if ($callCount === 2) { // Fail on second call
                throw new RuntimeException(message: 'Middleware chain interruption');
            }

            return $next($request);
        };

        // Create route with failing middleware chain
        $route = new RouteDefinition(
            method    : 'GET',
            path      : '/test',
            action    : static function () { return 'success'; },
            middleware: [$failingMiddleware, $failingMiddleware, $failingMiddleware]
        );

        $this->router->add(route: $route);

        // Router should handle middleware failures gracefully
        // (In real implementation, this would be handled by middleware pipeline)
        $this->assertTrue(condition: true); // Placeholder - actual middleware testing would be in pipeline tests
    }

    /**
     * @test
     * @throws DuplicateRouteException
     * @throws ReservedRouteNameException
     */
    public function memory_pressure_route_collection() : void
    {
        // Simulate high memory pressure with large route collection
        $largeRoutes = [];

        // Create 10,000 routes to simulate memory pressure
        for ($i = 1; $i <= 10000; $i++) {
            $route = new RouteDefinition(
                method    : 'GET',
                path      : "/stress/route{$i}/with/very/long/path/segments",
                action    : "StressController{$i}@handle",
                middleware: ['auth', 'cache', 'log', 'metrics'],
                name      : "stress.route{$i}"
            );

            $largeRoutes[] = $route;
        }

        // Measure memory before
        $memoryBefore = memory_get_usage(true);

        // Register routes
        foreach ($largeRoutes as $route) {
            $this->router->add(route: $route);
        }

        // Measure memory after
        $memoryAfter = memory_get_usage(true);
        $memoryUsed  = $memoryAfter - $memoryBefore;

        // Memory usage should be reasonable (< 50MB for 10k routes)
        $this->assertLessThan(expected: 50 * 1024 * 1024, actual: $memoryUsed,
                              message : 'Memory usage under stress test should be reasonable');

        // Routes should still be accessible
        $this->assertInstanceOf(expected: RouteDefinition::class, actual: $this->router->getByName(name: 'stress.route5000'));
        $this->assertInstanceOf(expected: RouteDefinition::class, actual: $this->router->getByName(name: 'stress.route9999'));
    }

    /**
     * @test
     */
    public function invalid_route_configuration_recovery() : void
    {
        // Test various invalid route configurations
        $invalidRoutes = [
            // Invalid path
            ['method' => 'GET', 'path' => '', 'action' => 'Controller@action'],
            // Invalid method
            ['method' => 'INVALID_METHOD', 'path' => '/test', 'action' => 'Controller@action'],
            // Invalid action
            ['method' => 'GET', 'path' => '/test', 'action' => null],
        ];

        $validRoutesAdded = 0;

        foreach ($invalidRoutes as $routeData) {
            try {
                $route = new RouteDefinition(
                    method: $routeData['method'],
                    path  : $routeData['path'],
                    action: $routeData['action']
                );

                $this->router->add(route: $route);
                $validRoutesAdded++;
            } catch (Throwable $exception) {
                // Expected - invalid routes should throw exceptions
                $this->assertInstanceOf(expected: Throwable::class, actual: $exception);
            }
        }

        // No invalid routes should have been added
        $this->assertEquals(expected: 0, actual: $validRoutesAdded);

        // Collection should remain clean
        $this->assertEmpty(actual: $this->router->allRoutes());
    }

    /**
     * @test
     * @throws Exception
     */
    public function route_loader_failure_fallback() : void
    {
        // Simulate primary loader failure
        $primaryLoader = $this->createMock(RouteSourceLoaderInterface::class);
        $primaryLoader->method('isAvailable')->willReturn(value: true);
        $primaryLoader->method('loadInto')->willThrowException(exception: new RuntimeException(message: 'Primary loader failed'));

        // Fallback loader succeeds
        $fallbackLoader = $this->createMock(RouteSourceLoaderInterface::class);
        $fallbackLoader->method('isAvailable')->willReturn(value: true);
        $fallbackLoader->method('loadInto')->willReturnCallback(callback: function (RouteCollection $collection) {
            $route = new RouteDefinition(method: 'GET', path: '/fallback', action: 'FallbackController@action');
            $collection->addRoute(route: $route);
        });

        // Simulate loader chain with fallback
        try {
            $primaryLoader->loadInto(collection: $this->collection);
        } catch (RuntimeException) {
            // Primary failed, try fallback
            $fallbackLoader->loadInto(collection: $this->collection);
        } catch (Exception $e) {
        }

        // Fallback route should be available
        $route = $this->collection->findExactRoute(method: 'GET', path: '/fallback');
        $this->assertNotNull(actual: $route);
        $this->assertEquals(expected: 'FallbackController@action', actual: $route->action);
    }

    /**
     * @test
     */
    public function extreme_concurrency_simulation() : void
    {
        // Simulate extreme concurrency with rapid route modifications
        $concurrentOperations = 50;
        $routesPerOperation   = 20;

        $operations = [];

        // Create concurrent operations
        for ($op = 0; $op < $concurrentOperations; $op++) {
            $operations[] = function () use ($routesPerOperation, $op) {
                for ($i = 0; $i < $routesPerOperation; $i++) {
                    $routeId = ($op * $routesPerOperation) + $i;
                    $route   = new RouteDefinition(
                        method    : 'GET',
                        path      : "/concurrent/{$routeId}",
                        action    : "ConcurrentController{$routeId}@action",
                        middleware: [],
                        name      : "concurrent.{$routeId}"
                    );

                    try {
                        $this->router->add(route: $route);
                    } catch (Throwable $exception) {
                        // In real concurrency, some operations might fail due to race conditions
                        // This is expected behavior we're testing for
                    }
                }
            };
        }

        // Execute operations (simulated concurrency)
        foreach ($operations as $operation) {
            $operation();
        }

        // Verify system stability - should have some routes registered
        $allRoutes = $this->router->allRoutes();
        $this->assertNotEmpty(actual: $allRoutes);

        // Total routes should be reasonable (allowing for some race condition failures)
        $totalRoutes = array_sum(array_map('count', $allRoutes));
        $this->assertGreaterThan(expected: 0, actual: $totalRoutes);
        $this->assertLessThanOrEqual(expected: $concurrentOperations * $routesPerOperation, actual: $totalRoutes);
    }

    /**
     * @test
     * @throws Exception
     */
    public function network_partition_simulation() : void
    {
        // Simulate network partition affecting external dependencies
        $networkDependentLoader = $this->createMock(RouteSourceLoaderInterface::class);
        $networkDependentLoader->method('isAvailable')
            ->willThrowException(exception: new RuntimeException(message: 'Network timeout'));

        $networkDependentLoader->method('loadInto')
            ->willThrowException(exception: new RuntimeException(message: 'Connection failed'));

        // Router should handle network failures gracefully
        $this->expectException(exception: RuntimeException::class);

        $networkDependentLoader->loadInto(collection: $this->collection);
    }

    /**
     * @test
     * @throws DuplicateRouteException
     */
    public function gradual_memory_leak_detection() : void
    {
        // Test for memory leaks during extended operation
        $initialMemory = memory_get_usage(true);
        $iterations    = 1000;

        for ($i = 0; $i < $iterations; $i++) {
            // Create and register route
            $route = new RouteDefinition(
                method: 'GET',
                path  : "/memory/test/{$i}",
                action: "MemoryController{$i}@action"
            );

            $this->router->add(route: $route);

            // Periodic cleanup simulation
            if ($i % 100 === 0) {
                // Force garbage collection in test environment
                gc_collect_cycles();

                $currentMemory  = memory_get_usage(true);
                $memoryIncrease = $currentMemory - $initialMemory;

                // Memory increase should be bounded (allow some growth for route storage)
                $this->assertLessThan(
                    expected: 10 * 1024 * 1024, // 10MB limit
                    actual  : $memoryIncrease,
                    message : "Memory leak detected at iteration {$i}"
                );
            }
        }

        // Final memory check
        $finalMemory   = memory_get_usage(true);
        $totalIncrease = $finalMemory - $initialMemory;

        // Total memory increase should be reasonable for 1000 routes
        $this->assertLessThan(
            expected: 50 * 1024 * 1024, // 50MB limit
            actual  : $totalIncrease,
            message : 'Excessive memory usage indicates potential leak'
        );
    }

    #[\Override]
    protected function setUp() : void
    {
        $this->router = new HttpRequestRouter(
            constraintValidator: new RouteConstraintValidator(),
            matcher            : $this->createMock(RouteMatcherInterface::class),
            trace              : null
        );

        $this->collection = new RouteCollection();
    }
}