<?php

declare(strict_types=1);

/**
 * Enterprise Router Performance Benchmarks
 *
 * Comprehensive benchmarking suite measuring router performance under
 * various conditions including route complexity, matching patterns,
 * and operational scenarios.
 *
 * Uses PhpBench framework for accurate micro-benchmarking.
 */

use Avax\HTTP\Router\Routing\HttpRequestRouter;
use Avax\HTTP\Router\Routing\RouteDefinition;
use Avax\HTTP\Router\Validation\RouteConstraintValidator;
use Avax\HTTP\Router\Matching\RouteMatcherInterface;
use Avax\HTTP\Request\Request;

/**
 * @BeforeMethods({"setUp"})
 */
class RouterBenchmark
{
    private HttpRequestRouter $router;
    private RouteMatcherInterface $matcher;
    private RouteConstraintValidator $validator;

    public function setUp(): void
    {
        $this->matcher = $this->createMock(RouteMatcherInterface::class);
        $this->validator = $this->createMock(RouteConstraintValidator::class);

        $this->router = new HttpRequestRouter(
            constraintValidator: $this->validator,
            matcher: $this->matcher
        );
    }

    /**
     * @Revs(1000)
     * @Iterations(10)
     * @OutputTimeUnit("milliseconds")
     */
    public function benchSimpleRouteRegistration(): void
    {
        $route = new RouteDefinition(
            method: 'GET',
            path: '/users/{id}',
            action: 'UserController@show',
            middleware: ['auth'],
            constraints: ['id' => '[0-9]+']
        );

        $this->router->add($route);
    }

    /**
     * @Revs(100)
     * @Iterations(5)
     * @OutputTimeUnit("milliseconds")
     */
    public function benchComplexRouteRegistration(): void
    {
        for ($i = 0; $i < 100; $i++) {
            $route = new RouteDefinition(
                method: 'POST',
                path: "/api/v1/resources/{$i}/subresources/{subId}/actions/{action}",
                action: "ResourceController@handle",
                middleware: ['api', 'auth', 'rate_limit', 'cache'],
                constraints: [
                    'subId' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}',
                    'action' => 'create|update|delete'
                ],
                name: "api.resource.{$i}.action"
            );

            $this->router->add($route);
        }
    }

    /**
     * @Revs(10000)
     * @Iterations(10)
     * @OutputTimeUnit("microseconds")
     */
    public function benchRouteLookupByName(): void
    {
        // Pre-populate with routes
        for ($i = 0; $i < 1000; $i++) {
            $route = new RouteDefinition(
                method: 'GET',
                path: "/benchmark/route/{$i}",
                action: "BenchmarkController{$i}@action",
                name: "benchmark.route.{$i}"
            );
            $this->router->add($route);
        }

        // Benchmark lookup
        $this->router->getByName('benchmark.route.500');
    }

    /**
     * @Revs(1000)
     * @Iterations(5)
     * @OutputTimeUnit("milliseconds")
     */
    public function benchRouteCollectionStatistics(): void
    {
        // Pre-populate with diverse routes
        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];
        $patterns = [
            '/simple/path',
            '/users/{id}',
            '/api/v1/resources/{resourceId}/sub/{subId}',
            '/complex/{param1}/{param2}/nested/{param3}/path',
        ];

        for ($i = 0; $i < 1000; $i++) {
            $route = new RouteDefinition(
                method: $methods[$i % count($methods)],
                path: $patterns[$i % count($patterns)],
                action: "Controller{$i}@action",
                middleware: ['middleware' . ($i % 5)],
                name: "route.{$i}"
            );
            $this->router->add($route);
        }

        // Benchmark statistics calculation
        $this->router->allRoutes();
    }

    /**
     * @Revs(100)
     * @Iterations(3)
     * @OutputTimeUnit("milliseconds")
     */
    public function benchLargeScaleRouteRegistration(): void
    {
        // Register 10,000 routes (enterprise-scale)
        for ($i = 0; $i < 10000; $i++) {
            $route = new RouteDefinition(
                method: 'GET',
                path: "/enterprise/resource/{$i}/endpoint",
                action: "EnterpriseController@handle",
                middleware: ['auth', 'log', 'metrics'],
                name: "enterprise.resource.{$i}"
            );
            $this->router->add($route);
        }
    }

    /**
     * @Revs(10000)
     * @Iterations(10)
     * @OutputTimeUnit("microseconds")
     * @ParamProviders({"provideRoutePatterns"})
     */
    public function benchRoutePatternMatching($params): void
    {
        $pattern = $params['pattern'];

        // Simple regex match simulation
        preg_match($pattern, '/users/123/posts/456/comments/789');
    }

    public function provideRoutePatterns(): array
    {
        return [
            ['pattern' => '#^/users/([^/]+)/posts/([^/]+)/comments/([^/]+)$#'],
            ['pattern' => '#^/api/v1/resources/([^/]+)/subresources/([^/]+)/actions/([^/]+)$#'],
            ['pattern' => '#^/complex/([^/]+)/path/([^/]+)/with/([^/]+)/many/([^/]+)/segments$#'],
            ['pattern' => '#^/wildcard/(.+)$#'],
        ];
    }

    /**
     * @Revs(1000)
     * @Iterations(5)
     * @OutputTimeUnit("milliseconds")
     * @ParamProviders({"provideMiddlewareStacks"})
     */
    public function benchMiddlewareStackProcessing($params): void
    {
        $middleware = $params['middleware'];

        // Simulate middleware stack processing
        $result = 'initial';
        foreach ($middleware as $mw) {
            $result = $this->simulateMiddleware($result, $mw);
        }
    }

    public function provideMiddlewareStacks(): array
    {
        return [
            ['middleware' => ['auth', 'session', 'csrf']],
            ['middleware' => ['auth', 'rate_limit', 'cache', 'log', 'metrics']],
            ['middleware' => ['auth', 'session', 'csrf', 'rate_limit', 'cache', 'log', 'metrics', 'cors']],
        ];
    }

    private function simulateMiddleware(string $input, string $middleware): string
    {
        // Simulate middleware processing overhead
        return hash('sha256', $input . $middleware);
    }

    /**
     * @Revs(100)
     * @Iterations(3)
     * @OutputTimeUnit("milliseconds")
     */
    public function benchDomainAwareRouting(): void
    {
        // Pre-populate with domain-specific routes
        $domains = ['api.example.com', 'admin.example.com', 'www.example.com'];
        $paths = ['/users', '/posts', '/comments', '/dashboard', '/reports'];

        foreach ($domains as $domain) {
            foreach ($paths as $path) {
                $route = new RouteDefinition(
                    method: 'GET',
                    path: $path,
                    action: 'DomainController@handle',
                    domain: $domain,
                    name: "{$domain}.{$path}"
                );
                $this->router->add($route);
            }
        }

        // Benchmark domain-aware lookups
        $this->router->getByName('api.example.com./users');
        $this->router->getByName('admin.example.com./dashboard');
    }

    /**
     * @Revs(500)
     * @Iterations(5)
     * @OutputTimeUnit("milliseconds")
     */
    public function benchRouteConstraintValidation(): void
    {
        // Test various constraint patterns
        $constraints = [
            'id' => '[0-9]+',
            'uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}',
            'slug' => '[a-z0-9]+(?:-[a-z0-9]+)*',
            'email' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}',
            'ipv4' => '(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)',
        ];

        $testValues = [
            'id' => '12345',
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'slug' => 'my-article-title',
            'email' => 'user@example.com',
            'ipv4' => '192.168.1.1',
        ];

        foreach ($constraints as $type => $pattern) {
            $value = $testValues[$type];
            preg_match("#^{$pattern}$#", $value);
        }
    }

    /**
     * @Revs(100)
     * @Iterations(3)
     * @OutputTimeUnit("milliseconds")
     */
    public function benchCacheSerialization(): void
    {
        // Create complex route collection for serialization testing
        $routes = [];
        for ($i = 0; $i < 1000; $i++) {
            $routes[] = new RouteDefinition(
                method: 'GET',
                path: "/cache/test/{$i}",
                action: ['CacheController', 'handle'],
                middleware: ['auth', 'cache'],
                constraints: ['id' => '[0-9]+'],
                name: "cache.test.{$i}"
            );
        }

        // Benchmark serialization
        $serialized = serialize($routes);

        // Benchmark deserialization
        unserialize($serialized);
    }

    /**
     * @Revs(1000)
     * @Iterations(5)
     * @OutputTimeUnit("microseconds")
     */
    public function benchReflectionCachePerformance(): void
    {
        // Test ReflectionCache performance
        $testClass = new class {
            public function testMethod(string $param): string {
                return $param;
            }

            public string $testProperty = 'value';
        };

        // Benchmark cached reflection operations
        \Avax\HTTP\Router\Routing\ReflectionCache::getClass($testClass::class);
        \Avax\HTTP\Router\Routing\ReflectionCache::getMethod($testClass, 'testMethod');
        \Avax\HTTP\Router\Routing\ReflectionCache::getProperty($testClass, 'testProperty');
        \Avax\HTTP\Router\Routing\ReflectionCache::hasMethod($testClass, 'testMethod');
        \Avax\HTTP\Router\Routing\ReflectionCache::isMethodPublic($testClass, 'testMethod');
    }

    /**
     * Memory usage benchmark
     *
     * @Revs(10)
     * @Iterations(1)
     * @OutputTimeUnit("milliseconds")
     */
    public function benchMemoryUsageScaling(): void
    {
        $initialMemory = memory_get_usage(true);

        // Scale from 100 to 10,000 routes
        for ($count = 100; $count <= 10000; $count *= 10) {
            $routes = [];
            for ($i = 0; $i < $count; $i++) {
                $routes[] = new RouteDefinition(
                    method: 'GET',
                    path: "/scale/test/{$i}",
                    action: "ScaleController@handle",
                    name: "scale.{$i}"
                );
            }

            $memoryAfter = memory_get_usage(true);
            $memoryPerRoute = ($memoryAfter - $initialMemory) / $count;

            // Log memory scaling characteristics
            error_log("Routes: {$count}, Memory per route: {$memoryPerRoute} bytes");
        }
    }
}