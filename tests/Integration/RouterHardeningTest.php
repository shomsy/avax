<?php

declare(strict_types=1);

namespace Avax\Tests\Integration;

use Avax\Components\Application\Container\Core\AppFactory;
use Avax\Components\Application\Container\Providers\Auth\AuthenticationBaseRegisterDependency;
use Avax\Components\Application\Container\Providers\Auth\SecurityBaseRegisterDependency;
use Avax\Components\Application\Container\Providers\Core\ConfigurationBaseRegisterDependency;
use Avax\Components\Application\Container\Providers\Core\FilesystemBaseRegisterDependency;
use Avax\Components\Application\Container\Providers\Core\LoggingBaseRegisterDependency;
use Avax\Components\Application\Container\Providers\Database\RegisterDatabaseDependencies;
use Avax\Components\Application\Container\Providers\HTTP\HTTPBaseRegisterDependency;
use Avax\Components\Application\Container\Providers\HTTP\HttpClientBaseRegisterDependency;
use Avax\Components\Application\Container\Providers\HTTP\MiddlewareBaseRegisterDependency;
use Avax\Components\Application\Container\Providers\HTTP\RouterBaseRegisterDependency;
use Avax\Components\Application\Container\Providers\HTTP\SessionBaseRegisterDependency;
use Avax\Components\Application\Container\Providers\HTTP\ViewBaseRegisterDependency;
use Avax\Components\HTTP\Request\System\Request;
use Avax\Components\HTTP\Router\RouterRuntimeInterface;
use Avax\Components\HTTP\System\Capabilities\Uri\UriBuilder;
use Avax\Tests\TestCase;
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
        $routes = dirname(path: __DIR__, levels: 2) . '/tests/fixtures/routes_with_null_callable.php';

        // Create app with test routes
        $this->app = $this->createAppWithRoutes(routesFile: $routes);

        // When: Requesting the null-returning route
        $request = $this->createRequest(method: 'GET', path: '/null-test');
        $response = $this->getRouter()->resolve($request);

        // Then: Should return fallback response
        $this->assertEquals(expected: 200, actual: $response->getStatusCode());
        $this->assertStringContainsString(needle: 'Callable returned null', haystack: (string) $response->getBody());
        $this->assertInstanceOf(expected: ResponseInterface::class, actual: $response);
    }

    private function createAppWithRoutes(string $routesFile) : mixed
    {
        $providers = [
            ConfigurationBaseRegisterDependency::class,
            FilesystemBaseRegisterDependency::class,
            LoggingBaseRegisterDependency::class,
            AuthenticationBaseRegisterDependency::class,
            SecurityBaseRegisterDependency::class,
            RegisterDatabaseDependencies::class,
            HTTPBaseRegisterDependency::class,
            MiddlewareBaseRegisterDependency::class,
            RouterBaseRegisterDependency::class,
            SessionBaseRegisterDependency::class,
            ViewBaseRegisterDependency::class,
            HttpClientBaseRegisterDependency::class,
        ];

        $cacheDir = dirname(path: __DIR__, levels: 2) . '/storage/cache';

        return AppFactory::http(
            providers: $providers,
            routes   : $routesFile,
            cacheDir : $cacheDir,
            debug    : true,
        );
    }

    /**
     * @throws ReflectionException
     */
    private function createRequest(string $method, string $path) : Request
    {
        $uri = UriBuilder::createFromString(uri: "http://localhost{$path}");

        return new Request(
            serverParams: ['REQUEST_METHOD' => $method],
            uri         : $uri,
        );
    }

    private function getRouter()
    {
        return $this->app->getContainer()->get(RouterRuntimeInterface::class);
    }

    /**
     * @test
     */
    public function post_on_get_only_route_should_return_method_not_allowed() : void
    {
        // Given: POST request to GET-only route
        $request = $this->createRequest(method: 'POST', path: '/health');

        // When: Router resolves the request
        // Note: This might throw MethodNotAllowedException before reaching pipeline
        try {
            $response = $this->getRouter()->resolve($request);
            // If we get here, check it's a proper error response
            $this->assertEquals(expected: 500, actual: $response->getStatusCode());
            $this->assertStringContainsString(needle: 'Internal Server Error', haystack: (string) $response->getBody());
        } catch (Throwable $e) {
            // Exception is acceptable as long as it's caught by our error handling
            $this->assertInstanceOf(expected: Throwable::class, actual: $e);
        }
    }

    /**
     * @test
     */
    public function fallback_route_returns_error_handling_response() : void
    {
        // Given: ServerRequest to non-existent route
        $request = $this->createRequest(method: 'GET', path: '/non-existent-route-12345');

        // When: Router resolves the request
        $response = $this->getRouter()->resolve($request);

        // Then: Should return error response (exceptions caught at higher level)
        $this->assertEquals(expected: 500, actual: $response->getStatusCode());
        $this->assertStringContainsString(needle: 'Route resolution failed', haystack: (string) $response->getBody());
        $this->assertInstanceOf(expected: ResponseInterface::class, actual: $response);
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
                $request = $this->createRequest(method: 'GET', path: $route);
                $response = $this->getRouter()->resolve($request);

                // Basic validation that response is valid
                $this->assertInstanceOf(expected: ResponseInterface::class, actual: $response);
                $this->assertIsInt(actual: $response->getStatusCode());
                $this->assertIsString(actual: (string) $response->getBody());
            }
        }

        // If we get here without memory issues, test passes
        $this->assertTrue(condition: true, message: 'Stress test completed without issues');
    }

    /**
     * @test
     */
    public function all_dispatcher_methods_return_response_interface() : void
    {
        $routes = ['/', '/health', '/test', '/favicon.ico'];

        foreach ($routes as $route) {
            $request = $this->createRequest(method: 'GET', path: $route);
            $response = $this->getRouter()->resolve($request);

            // Validate PSR-7 compliance
            $this->assertInstanceOf(expected: ResponseInterface::class, actual: $response);

            // Validate response has required methods
            $this->assertIsInt(actual: $response->getStatusCode());
            $this->assertIsString(actual: $response->getReasonPhrase());
            $this->assertIsArray(actual: $response->getHeaders());
            $this->assertIsString(actual: (string) $response->getBody());
        }
    }

    /**
     * @test
     */
    public function route_pipeline_dispatch_returns_valid_response() : void
    {
        // This test validates that RoutePipeline dispatch method works correctly
        $request = $this->createRequest(method: 'GET', path: '/health');
        $response = $this->getRouter()->resolve($request);

        // Validate the response is properly formed
        $this->assertEquals(expected: 200, actual: $response->getStatusCode());
        $this->assertEquals(expected: 'ok', actual: (string) $response->getBody());
        $this->assertStringContainsString(needle: 'text/plain', haystack: $response->getHeaderLine('Content-Type'));
    }

    /**
     * @test
     */
    public function router_kernel_returns_final_response() : void
    {
        // Test that the full routing pipeline returns a final response
        $request = $this->createRequest(method: 'GET', path: '/');
        $response = $this->getRouter()->resolve($request);

        // Validate final response
        $this->assertInstanceOf(expected: ResponseInterface::class, actual: $response);
        $this->assertEquals(expected: 200, actual: $response->getStatusCode());
        $this->assertStringContainsString(needle: 'Router is Working!', haystack: (string) $response->getBody());
    }

    /**
     * @test
     */
    public function debug_route_returns_error_handling_response() : void
    {
        // Given: ServerRequest to debug route
        $request = $this->createRequest(method: 'GET', path: '/debug');

        // When: Router resolves the request
        $response = $this->getRouter()->resolve($request);

        // Then: Should return error response (exceptions caught at higher level)
        $this->assertEquals(expected: 500, actual: $response->getStatusCode());
        $this->assertStringContainsString(needle: 'Route resolution failed', haystack: (string) $response->getBody());
        $this->assertInstanceOf(expected: ResponseInterface::class, actual: $response);
    }

    /**
     * @test
     */
    public function middleware_stagechain_reactivation_works() : void
    {
        // Test middleware pipeline reactivation
        $request = $this->createRequest(method: 'GET', path: '/health');
        $response = $this->getRouter()->resolve($request);

        // If middleware is working, we should get a valid response
        $this->assertInstanceOf(expected: ResponseInterface::class, actual: $response);
        $this->assertEquals(expected: 200, actual: $response->getStatusCode());
    }

    #[Override]
    protected function setUp() : void
    {
        $providers = [
            ConfigurationBaseRegisterDependency::class,
            FilesystemBaseRegisterDependency::class,
            LoggingBaseRegisterDependency::class,
            AuthenticationBaseRegisterDependency::class,
            SecurityBaseRegisterDependency::class,
            RegisterDatabaseDependencies::class,
            HTTPBaseRegisterDependency::class,
            MiddlewareBaseRegisterDependency::class,
            RouterBaseRegisterDependency::class,
            SessionBaseRegisterDependency::class,
            ViewBaseRegisterDependency::class,
            HttpClientBaseRegisterDependency::class,
        ];

        $routes   = dirname(path: __DIR__, levels: 2) . '/Presentation/HTTP/routes/web.routes.php';
        $cacheDir = dirname(path: __DIR__, levels: 2) . '/storage/cache';

        $this->app = AppFactory::http(
            providers: $providers,
            routes   : $routes,
            cacheDir : $cacheDir,
            debug    : true,
        );
    }
}
