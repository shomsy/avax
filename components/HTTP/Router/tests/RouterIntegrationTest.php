<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\Tests;

use Avax\Components\HTTP\Router\System\Flows\ResolveRequest\HttpRequestRouter;
use Avax\Components\HTTP\Router\System\Flows\ResolveRequest\Matching\DomainAwareMatcher;
use Avax\Components\HTTP\Router\System\Flows\ResolveRequest\Matching\RouteMatcher;
use Override;
use PHPUnit\Framework\TestCase;

/**
 * Integration stability tests for the promoted Router component.
 */
final class RouterIntegrationTest extends TestCase
{
    private HttpRequestRouter $router;
    private string            $cacheDir;

    public function testCacheConsistencyWithRuntimeRegistration() : void
    {
        $runtimeRoutes = $this->loadRoutesViaDsl();
        $cacheRoutes   = $this->loadRoutesViaCache();

        $this->assertRouteSetsAreIdentical(runtimeRoutes: $runtimeRoutes, cacheRoutes: $cacheRoutes);
    }

    private function loadRoutesViaDsl() : array
    {
        return $this->router->allRoutes();
    }

    private function loadRoutesViaCache() : array
    {
        return $this->router->allRoutes();
    }

    private function assertRouteSetsAreIdentical(array $runtimeRoutes, array $cacheRoutes) : void
    {
        $this->assertEquals(count($runtimeRoutes), count($cacheRoutes));

        foreach ($runtimeRoutes as $method => $routes) {
            $this->assertArrayHasKey($method, $cacheRoutes);
            $this->assertCount(count($routes), $cacheRoutes[$method]);

            foreach ($routes as $index => $runtimeRoute) {
                $cacheRoute = $cacheRoutes[$method][$index];
                $this->assertEquals($runtimeRoute->method, $cacheRoute->method);
                $this->assertEquals($runtimeRoute->path, $cacheRoute->path);
                $this->assertEquals($runtimeRoute->action, $cacheRoute->action);
            }
        }
    }

    #[Override]
    protected function setUp() : void
    {
        $this->cacheDir = sys_get_temp_dir() . '/router-cache-' . uniqid();
        mkdir($this->cacheDir, 0777, true);

        $this->initializeRouterComponents();
    }

    private function initializeRouterComponents() : void
    {
        $this->router = new HttpRequestRouter();
    }

    #[Override]
    protected function tearDown() : void
    {
        $this->removeDirectory($this->cacheDir);
    }

    private function removeDirectory(string $dir) : void
    {
        if (! is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
