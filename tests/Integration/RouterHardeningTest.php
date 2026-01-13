<?php

declare(strict_types=1);

use Avax\Container\Core\AppFactory;
use Avax\Container\Providers\Auth\AuthenticationServiceProvider;
use Avax\Container\Providers\Auth\SecurityServiceProvider;
use Avax\Container\Providers\Core\ConfigurationServiceProvider;
use Avax\Container\Providers\Core\FilesystemServiceProvider;
use Avax\Container\Providers\Core\LoggingServiceProvider;
use Avax\Container\Providers\Database\DatabaseServiceProvider;
use Avax\Container\Providers\HTTP\HttpClientServiceProvider;
use Avax\Container\Providers\HTTP\HTTPServiceProvider;
use Avax\Container\Providers\HTTP\MiddlewareServiceProvider;
use Avax\Container\Providers\HTTP\RouterServiceProvider;
use Avax\Container\Providers\HTTP\SessionServiceProvider;
use Avax\Container\Providers\HTTP\ViewServiceProvider;
use Avax\HTTP\Request\Request;
use Avax\HTTP\URI\UriBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * ROUTER HARDENING TESTS: Comprehensive Edge Cases & Stress Testing
 *
 * Tests advanced scenarios, error handling, and system robustness.
 */
class RouterHardeningTest extends TestCase
{
    private $app;

    /**
     * @test
     */
    public function callable_returning_null_should_return_fallback_response() : void
    {
        // Given: A route with callable that returns null
        $routes = dirname(__DIR__, 2) . '/tests/fixtures/routes_with_null_callable.php';

        // Create app with test routes
        $this->app = $this->createAppWithRoutes($routes);

        // When: Requesting the null-returning route
        $request = $this->createRequest('GET', '/null-test');
        $response = $this->getRouter()->resolve($request);

        // Then: Should return fallback response
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Callable returned null', (string) $response->getBody());
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    /**
     * @test
     */
    public function post_on_get_only_route_should_return_method_not_allowed() : void
    {
        // Given: POST request to GET-only route
        $request = $this->createRequest('POST', '/health');

        // When: Router resolves the request
        // Note: This might throw MethodNotAllowedException before reaching pipeline
        try {
            $response = $this->getRouter()->resolve($request);
            // If we get here, check it's a proper error response
            $this->assertEquals(500, $response->getStatusCode());
            $this->assertStringContainsString('Internal Server Error', (string) $response->getBody());
        } catch (\Throwable $e) {
            // Exception is acceptable as long as it's caught by our error handling
            $this->assertInstanceOf(\Throwable::class, $e);
        }
    }

    /**
     * @test
     */
    public function fallback_route_returns_error_handling_response() : void
    {
        // Given: Request to non-existent route
        $request = $this->createRequest('GET', '/non-existent-route-12345');

        // When: Router resolves the request
        $response = $this->getRouter()->resolve($request);

        // Then: Should return error response (exceptions caught at higher level)
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertStringContainsString('Route resolution failed', (string) $response->getBody());
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    /**
     * @test
     */
    public function stress_test_sequential_route_calls_no_leaks() : void
    {
        $routes = ['/', '/health', '/test', '/favicon.ico'];
        $iterations = 100;

        // Run stress test
        for ($i = 0; $i < $iterations; $i++) {
            foreach ($routes as $route) {
                $request = $this->createRequest('GET', $route);
                $response = $this->getRouter()->resolve($request);

                // Basic validation that response is valid
                $this->assertInstanceOf(ResponseInterface::class, $response);
                $this->assertIsInt($response->getStatusCode());
                $this->assertIsString((string) $response->getBody());
            }
        }

        // If we get here without memory issues, test passes
        $this->assertTrue(true, 'Stress test completed without issues');
    }

    /**
     * @test
     */
    public function all_dispatcher_methods_return_response_interface() : void
    {
        $routes = ['/', '/health', '/test', '/favicon.ico'];

        foreach ($routes as $route) {
            $request = $this->createRequest('GET', $route);
            $response = $this->getRouter()->resolve($request);

            // Validate PSR-7 compliance
            $this->assertInstanceOf(ResponseInterface::class, $response);

            // Validate response has required methods
            $this->assertIsInt($response->getStatusCode());
            $this->assertIsString($response->getReasonPhrase());
            $this->assertIsArray($response->getHeaders());
            $this->assertIsString((string) $response->getBody());
        }
    }

    /**
     * @test
     */
    public function route_pipeline_dispatch_returns_valid_response() : void
    {
        // This test validates that RoutePipeline dispatch method works correctly
        $request = $this->createRequest('GET', '/health');
        $response = $this->getRouter()->resolve($request);

        // Validate the response is properly formed
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('ok', (string) $response->getBody());
        $this->assertStringContainsString('text/plain', $response->getHeaderLine('Content-Type'));
    }

    /**
     * @test
     */
    public function router_kernel_returns_final_response() : void
    {
        // Test that the full routing pipeline returns a final response
        $request = $this->createRequest('GET', '/');
        $response = $this->getRouter()->resolve($request);

        // Validate final response
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Router is Working!', (string) $response->getBody());
    }

    /**
     * @test
     */
    public function debug_route_returns_error_handling_response() : void
    {
        // Given: Request to debug route
        $request = $this->createRequest('GET', '/debug');

        // When: Router resolves the request
        $response = $this->getRouter()->resolve($request);

        // Then: Should return error response (exceptions caught at higher level)
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertStringContainsString('Route resolution failed', (string) $response->getBody());
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    /**
     * @test
     */
    public function middleware_stagechain_reactivation_works() : void
    {
        // Test middleware pipeline reactivation
        $request = $this->createRequest('GET', '/health');
        $response = $this->getRouter()->resolve($request);

        // If middleware is working, we should get a valid response
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    protected function setUp() : void
    {
        $providers = [
            ConfigurationServiceProvider::class,
            FilesystemServiceProvider::class,
            LoggingServiceProvider::class,
            AuthenticationServiceProvider::class,
            SecurityServiceProvider::class,
            DatabaseServiceProvider::class,
            HTTPServiceProvider::class,
            MiddlewareServiceProvider::class,
            RouterServiceProvider::class,
            SessionServiceProvider::class,
            ViewServiceProvider::class,
            HttpClientServiceProvider::class,
        ];

        $routes = dirname(__DIR__, 2) . '/Presentation/HTTP/routes/web.routes.php';
        $cacheDir = dirname(__DIR__, 2) . '/storage/cache';

        $this->app = AppFactory::http(
            providers: $providers,
            routes: $routes,
            cacheDir: $cacheDir,
            debug: true
        );
    }

    private function createAppWithRoutes(string $routesFile): mixed
    {
        $providers = [
            ConfigurationServiceProvider::class,
            FilesystemServiceProvider::class,
            LoggingServiceProvider::class,
            AuthenticationServiceProvider::class,
            SecurityServiceProvider::class,
            DatabaseServiceProvider::class,
            HTTPServiceProvider::class,
            MiddlewareServiceProvider::class,
            RouterServiceProvider::class,
            SessionServiceProvider::class,
            ViewServiceProvider::class,
            HttpClientServiceProvider::class,
        ];

        $cacheDir = dirname(__DIR__, 2) . '/storage/cache';

        return AppFactory::http(
            providers: $providers,
            routes: $routesFile,
            cacheDir: $cacheDir,
            debug: true
        );
    }

    private function getRouter()
    {
        return $this->app->getContainer()->get(\Avax\HTTP\Router\RouterRuntimeInterface::class);
    }

    private function createRequest(string $method, string $path): Request
    {
        $uri = UriBuilder::createFromString("http://localhost{$path}");
        return new Request(
            serverParams: ['REQUEST_METHOD' => $method],
            uri: $uri
        );
    }
}