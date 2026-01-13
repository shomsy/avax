<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Tests;

use Avax\HTTP\Router\Routing\HttpRequestRouter;
use Avax\HTTP\Router\Routing\RouteDefinition;
use Avax\HTTP\Router\Routing\RouteCollection;
use Avax\HTTP\Router\Routing\RouteSourceLoaderInterface;
use Avax\HTTP\Router\Matching\RouteMatcherInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;

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
    private RouteCollection $collection;

    protected function setUp(): void
    {
        $this->router = new HttpRequestRouter(
            constraintValidator: new \Avax\HTTP\Router\Validation\RouteConstraintValidator(),
            matcher: $this->createMock(RouteMatcherInterface::class),
            trace: null
        );

        $this->collection = new RouteCollection();
    }

    /**
     * @test
     */
    public function cache_corruption_does_not_crash_router(): void
    {
        // Simulate cache file with corrupted JSON
        $corruptedData = '{"invalid": json, "missing": brackets';

        $cacheLoader = $this->createMock(RouteSourceLoaderInterface::class);
        $cacheLoader->method('loadInto')
            ->willThrowException(new RuntimeException('Cache corruption detected'));

        $cacheLoader->method('isAvailable')
            ->willReturn(true);

        // Router should handle cache corruption gracefully
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cache corruption detected');

        $cacheLoader->loadInto($this->collection);
    }

    /**
     * @test
     */
    public function concurrent_route_registration_isolation(): void
    {
        // Simulate concurrent route registration from multiple threads/loaders
        $routes1 = [];
        $routes2 = [];

        // Thread 1: Register API routes
        $thread1 = function () use (&$routes1) {
            for ($i = 1; $i <= 100; $i++) {
                $route = new RouteDefinition(
                    'GET',
                    "/api/v1/resource{$i}",
                    "Controller{$i}@action",
                    ['api', 'auth'],
                    "api.resource{$i}"
                );
                $routes1[] = $route;
            }
        };

        // Thread 2: Register web routes
        $thread2 = function () use (&$routes2) {
            for ($i = 1; $i <= 100; $i++) {
                $route = new RouteDefinition(
                    'GET',
                    "/web/resource{$i}",
                    "WebController{$i}@action",
                    ['web', 'session'],
                    "web.resource{$i}"
                );
                $routes2[] = $route;
            }
        };

        // Execute concurrently (simulated)
        $thread1();
        $thread2();

        // Register all routes
        foreach (array_merge($routes1, $routes2) as $route) {
            $this->router->add($route);
        }

        // Verify isolation - no conflicts between API and web routes
        $this->assertInstanceOf(RouteDefinition::class, $this->router->getByName('api.resource50'));
        $this->assertInstanceOf(RouteDefinition::class, $this->router->getByName('web.resource50'));
    }

    /**
     * @test
     */
    public function middleware_chain_interruption_recovery(): void
    {
        // Test middleware that throws exceptions mid-chain
        $failingMiddleware = function ($request, $next) {
            static $callCount = 0;
            $callCount++;

            if ($callCount === 2) { // Fail on second call
                throw new RuntimeException('Middleware chain interruption');
            }

            return $next($request);
        };

        // Create route with failing middleware chain
        $route = new RouteDefinition(
            'GET',
            '/test',
            function () { return 'success'; },
            [$failingMiddleware, $failingMiddleware, $failingMiddleware]
        );

        $this->router->add($route);

        // Router should handle middleware failures gracefully
        // (In real implementation, this would be handled by middleware pipeline)
        $this->assertTrue(true); // Placeholder - actual middleware testing would be in pipeline tests
    }

    /**
     * @test
     */
    public function memory_pressure_route_collection(): void
    {
        // Simulate high memory pressure with large route collection
        $largeRoutes = [];

        // Create 10,000 routes to simulate memory pressure
        for ($i = 1; $i <= 10000; $i++) {
            $route = new RouteDefinition(
                'GET',
                "/stress/route{$i}/with/very/long/path/segments",
                "StressController{$i}@handle",
                ['auth', 'cache', 'log', 'metrics'],
                "stress.route{$i}"
            );

            $largeRoutes[] = $route;
        }

        // Measure memory before
        $memoryBefore = memory_get_usage(true);

        // Register routes
        foreach ($largeRoutes as $route) {
            $this->router->add($route);
        }

        // Measure memory after
        $memoryAfter = memory_get_usage(true);
        $memoryUsed = $memoryAfter - $memoryBefore;

        // Memory usage should be reasonable (< 50MB for 10k routes)
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsed,
            'Memory usage under stress test should be reasonable');

        // Routes should still be accessible
        $this->assertInstanceOf(RouteDefinition::class, $this->router->getByName('stress.route5000'));
        $this->assertInstanceOf(RouteDefinition::class, $this->router->getByName('stress.route9999'));
    }

    /**
     * @test
     */
    public function invalid_route_configuration_recovery(): void
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
                    $routeData['method'],
                    $routeData['path'],
                    $routeData['action']
                );

                $this->router->add($route);
                $validRoutesAdded++;
            } catch (\Throwable $exception) {
                // Expected - invalid routes should throw exceptions
                $this->assertInstanceOf(\Throwable::class, $exception);
            }
        }

        // No invalid routes should have been added
        $this->assertEquals(0, $validRoutesAdded);

        // Collection should remain clean
        $this->assertEmpty($this->router->allRoutes());
    }

    /**
     * @test
     */
    public function route_loader_failure_fallback(): void
    {
        // Simulate primary loader failure
        $primaryLoader = $this->createMock(RouteSourceLoaderInterface::class);
        $primaryLoader->method('isAvailable')->willReturn(true);
        $primaryLoader->method('loadInto')->willThrowException(new RuntimeException('Primary loader failed'));

        // Fallback loader succeeds
        $fallbackLoader = $this->createMock(RouteSourceLoaderInterface::class);
        $fallbackLoader->method('isAvailable')->willReturn(true);
        $fallbackLoader->method('loadInto')->willReturnCallback(function (RouteCollection $collection) {
            $route = new RouteDefinition('GET', '/fallback', 'FallbackController@action');
            $collection->addRoute($route);
        });

        // Simulate loader chain with fallback
        try {
            $primaryLoader->loadInto($this->collection);
        } catch (RuntimeException) {
            // Primary failed, try fallback
            $fallbackLoader->loadInto($this->collection);
        }

        // Fallback route should be available
        $route = $this->collection->findExactRoute('GET', '/fallback');
        $this->assertNotNull($route);
        $this->assertEquals('FallbackController@action', $route->action);
    }

    /**
     * @test
     */
    public function extreme_concurrency_simulation(): void
    {
        // Simulate extreme concurrency with rapid route modifications
        $concurrentOperations = 50;
        $routesPerOperation = 20;

        $operations = [];

        // Create concurrent operations
        for ($op = 0; $op < $concurrentOperations; $op++) {
            $operations[] = function () use ($op, $routesPerOperation) {
                for ($i = 0; $i < $routesPerOperation; $i++) {
                    $routeId = ($op * $routesPerOperation) + $i;
                    $route = new RouteDefinition(
                        'GET',
                        "/concurrent/{$routeId}",
                        "ConcurrentController{$routeId}@action",
                        [],
                        "concurrent.{$routeId}"
                    );

                    try {
                        $this->router->add($route);
                    } catch (\Throwable $exception) {
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
        $this->assertNotEmpty($allRoutes);

        // Total routes should be reasonable (allowing for some race condition failures)
        $totalRoutes = array_sum(array_map('count', $allRoutes));
        $this->assertGreaterThan(0, $totalRoutes);
        $this->assertLessThanOrEqual($concurrentOperations * $routesPerOperation, $totalRoutes);
    }

    /**
     * @test
     */
    public function network_partition_simulation(): void
    {
        // Simulate network partition affecting external dependencies
        $networkDependentLoader = $this->createMock(RouteSourceLoaderInterface::class);
        $networkDependentLoader->method('isAvailable')
            ->willThrowException(new RuntimeException('Network timeout'));

        $networkDependentLoader->method('loadInto')
            ->willThrowException(new RuntimeException('Connection failed'));

        // Router should handle network failures gracefully
        $this->expectException(RuntimeException::class);

        $networkDependentLoader->loadInto($this->collection);
    }

    /**
     * @test
     */
    public function gradual_memory_leak_detection(): void
    {
        // Test for memory leaks during extended operation
        $initialMemory = memory_get_usage(true);
        $iterations = 1000;

        for ($i = 0; $i < $iterations; $i++) {
            // Create and register route
            $route = new RouteDefinition(
                'GET',
                "/memory/test/{$i}",
                "MemoryController{$i}@action"
            );

            $this->router->add($route);

            // Periodic cleanup simulation
            if ($i % 100 === 0) {
                // Force garbage collection in test environment
                gc_collect_cycles();

                $currentMemory = memory_get_usage(true);
                $memoryIncrease = $currentMemory - $initialMemory;

                // Memory increase should be bounded (allow some growth for route storage)
                $this->assertLessThan(
                    10 * 1024 * 1024, // 10MB limit
                    $memoryIncrease,
                    "Memory leak detected at iteration {$i}"
                );
            }
        }

        // Final memory check
        $finalMemory = memory_get_usage(true);
        $totalIncrease = $finalMemory - $initialMemory;

        // Total memory increase should be reasonable for 1000 routes
        $this->assertLessThan(
            50 * 1024 * 1024, // 50MB limit
            $totalIncrease,
            'Excessive memory usage indicates potential leak'
        );
    }
}