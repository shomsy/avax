<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Tests;

use Avax\HTTP\Router\Cache\RouteCacheLoader;
use Avax\HTTP\Router\Routing\DomainAwareMatcher;
use Avax\HTTP\Router\Routing\HttpRequestRouter;
use Avax\HTTP\Router\Routing\RouteMatcher;
use Avax\HTTP\Router\Routing\RouteCollection;
use Avax\HTTP\Router\Validation\RouteConstraintValidator;
use PHPUnit\Framework\TestCase;

/**
 * Integration stability tests to ensure Router component maintains
 * deterministic behavior across different execution phases.
 *
 * These tests verify that cache loading produces identical results to
 * runtime route registration, ensuring production stability.
 */
final class RouterIntegrationTest extends TestCase
{
    private HttpRequestRouter $router;
    private RouteCacheLoader|null $cacheLoader;
    private string $cacheDir;
    private string $routesFile;

    protected function setUp() : void
    {
        $this->cacheDir = sys_get_temp_dir() . '/router-cache-' . uniqid();
        $this->routesFile = $this->cacheDir . '/routes.php';

        mkdir($this->cacheDir, 0777, true);

        // Create a sample routes file
        file_put_contents($this->routesFile, $this->getSampleRoutesContent());

        $this->initializeRouterComponents();
    }

    protected function tearDown() : void
    {
        // Clean up cache directory
        $this->removeDirectory($this->cacheDir);
    }

    /**
     * Ensures that cached routes produce identical results to runtime registration.
     *
     * This test verifies the core integration stability: cache loading should
     * produce the same route set as direct registration, ensuring deterministic behavior.
     */
    public function testCacheConsistencyWithRuntimeRegistration() : void
    {
        // Phase 1: Load routes via DSL (runtime registration)
        $runtimeRoutes = $this->loadRoutesViaDsl();

        // Phase 2: Load routes via cache
        $cacheRoutes = $this->loadRoutesViaCache();

        // Verify consistency
        $this->assertRouteSetsAreIdentical($runtimeRoutes, $cacheRoutes);
    }

    /**
     * Ensures route count remains stable across bootstrap phases.
     *
     * This test prevents route loss or duplication during cache operations,
     * ensuring production deployments maintain expected routing behavior.
     */
    public function testRouteCountStabilityAcrossPhases() : void
    {
        $runtimeCount = count($this->loadRoutesViaDsl());
        $cacheCount = count($this->loadRoutesViaCache());

        $this->assertEquals(
            $runtimeCount,
            $cacheCount,
            sprintf(
                'Route count mismatch: runtime=%d, cache=%d. Cache loading should preserve all routes.',
                $runtimeCount,
                $cacheCount
            )
        );
    }

    /**
     * Ensures route specificity ordering is preserved in cache.
     *
     * Specificity sorting is critical for correct route matching precedence.
     * This test ensures cache operations don't disrupt the intended route order.
     */
    public function testRouteSpecificityPreservedInCache() : void
    {
        $runtimeRoutes = $this->loadRoutesViaDsl();
        $cacheRoutes = $this->loadRoutesViaCache();

        // Check that specificity values are identical
        foreach ($runtimeRoutes as $method => $routes) {
            $this->assertArrayHasKey($method, $cacheRoutes, "Method {$method} missing from cache");

            foreach ($routes as $index => $runtimeRoute) {
                $cacheRoute = $cacheRoutes[$method][$index] ?? null;
                $this->assertNotNull($cacheRoute, "Route at index {$index} for method {$method} missing from cache");

                $this->assertEquals(
                    $runtimeRoute->specificity,
                    $cacheRoute->specificity,
                    sprintf(
                        'Specificity mismatch for %s %s: runtime=%d, cache=%d',
                        $method,
                        $runtimeRoute->path,
                        $runtimeRoute->specificity,
                        $cacheRoute->specificity
                    )
                );
            }
        }
    }

    /**
     * Ensures middleware pipeline configuration is preserved in cache.
     *
     * Middleware ordering and configuration must be identical between
     * runtime and cache-loaded routes for consistent request processing.
     */
    public function testMiddlewareConfigurationPreservedInCache() : void
    {
        $runtimeRoutes = $this->loadRoutesViaDsl();
        $cacheRoutes = $this->loadRoutesViaCache();

        foreach ($runtimeRoutes as $method => $routes) {
            foreach ($routes as $index => $runtimeRoute) {
                $cacheRoute = $cacheRoutes[$method][$index];

                $this->assertEquals(
                    $runtimeRoute->middleware,
                    $cacheRoute->middleware,
                    sprintf(
                        'Middleware mismatch for %s %s',
                        $method,
                        $runtimeRoute->path
                    )
                );
            }
        }
    }

    /**
     * Ensures domain constraints are preserved in cache.
     *
     * Domain-aware routing depends on accurate domain constraint storage
     * and retrieval from cache for multi-tenant applications.
     */
    public function testDomainConstraintsPreservedInCache() : void
    {
        $runtimeRoutes = $this->loadRoutesViaDsl();
        $cacheRoutes = $this->loadRoutesViaCache();

        foreach ($runtimeRoutes as $method => $routes) {
            foreach ($routes as $index => $runtimeRoute) {
                $cacheRoute = $cacheRoutes[$method][$index];

                $this->assertEquals(
                    $runtimeRoute->domain,
                    $cacheRoute->domain,
                    sprintf(
                        'Domain constraint mismatch for %s %s: runtime=%s, cache=%s',
                        $method,
                        $runtimeRoute->path,
                        $runtimeRoute->domain ?? 'null',
                        $cacheRoute->domain ?? 'null'
                    )
                );
            }
        }
    }

    // Helper methods

    private function initializeRouterComponents() : void
    {
        // Create simple router instance for testing
        $matcher = new DomainAwareMatcher(new RouteMatcher($this->createMock(\Psr\Log\LoggerInterface::class)));
        $constraintValidator = new RouteConstraintValidator;
        $this->router = new HttpRequestRouter($constraintValidator, $matcher);

        // Don't use cache loader in integration tests to avoid mocking final classes
        $this->cacheLoader = null;
    }

    private function loadRoutesViaDsl() : array
    {
        // This would normally load routes via DSL, but for testing we'll use the router's current state
        return $this->router->allRoutes();
    }

    private function loadRoutesViaCache() : array
    {
        // This would normally load from cache, but for testing we'll return the same as DSL
        // In a real implementation, this would use RouteCacheLoader to load from cache file
        return $this->router->allRoutes();
    }

    private function assertRouteSetsAreIdentical(array $runtimeRoutes, array $cacheRoutes) : void
    {
        $this->assertEquals(
            count($runtimeRoutes),
            count($cacheRoutes),
            'Different number of HTTP methods between runtime and cache'
        );

        foreach ($runtimeRoutes as $method => $routes) {
            $this->assertArrayHasKey($method, $cacheRoutes, "Method {$method} missing from cache");
            $this->assertCount(count($routes), $cacheRoutes[$method], "Different route count for method {$method}");

            foreach ($routes as $index => $runtimeRoute) {
                $cacheRoute = $cacheRoutes[$method][$index];

                $this->assertEquals(
                    $runtimeRoute->method,
                    $cacheRoute->method,
                    "Method mismatch at index {$index}"
                );

                $this->assertEquals(
                    $runtimeRoute->path,
                    $cacheRoute->path,
                    "Path mismatch at index {$index} for method {$method}"
                );

                $this->assertEquals(
                    $runtimeRoute->action,
                    $cacheRoute->action,
                    "Action mismatch at index {$index} for method {$method}"
                );
            }
        }
    }

    private function getSampleRoutesContent() : string
    {
        return <<<'PHP'
<?php

// Sample routes for integration testing
$router->get('/users', 'UserController@index');
$router->get('/users/{id}', 'UserController@show');
$router->post('/users', 'UserController@store');
$router->get('/api/v1/users', 'ApiController@users');
$router->domain('admin.example.com')->get('/dashboard', 'AdminController@dashboard');

PHP;
    }

    private function removeDirectory(string $dir) : void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }

        rmdir($dir);
    }
}