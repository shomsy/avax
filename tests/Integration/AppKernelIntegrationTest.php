<?php

declare(strict_types=1);

namespace Avax\Tests\Integration;

use Avax\Components\HTTP\Response\System\PublicSurface\Responses;
use Avax\HTTP\AppKernel;
use Avax\HTTP\Dispatcher\ControllerDispatcher;
use Avax\HTTP\Middleware\CsrfVerificationMiddleware;
use Avax\HTTP\Router\RouterInterface;
use Avax\HTTP\Router\System\Capabilities\RouteDefinition\RouteCollection;
use Avax\HTTP\RouterBootstrapper;
use Avax\Tests\TestCase;
use components\HTTP\Router\System\Foundation\Exceptions\ReservedRouteNameException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * INTEGRATION TESTS: Complete Application Assembly
 *
 * Tests RouterBootstrapper + AppKernel + PSR-15 Middleware end-to-end.
 */
class AppKernelIntegrationTest extends TestCase
{
    private RouterInterface $router;

    private ControllerDispatcher $dispatcher;

    private Responses $responseFactory;

    private RouterBootstrapper $bootstrapper;

    /**
     * @test
     *
     * @throws ReservedRouteNameException
     * @throws ReservedRouteNameException
     * @throws ReservedRouteNameException
     */
    public function router_bootstrapper_registers_routes() : void
    {
        // Given: Routes registered via bootstrapper
        $this->bootstrapper
            ->get(path: '/users', handler: [UserController::class, 'index'])
            ->post(path: '/users', handler: [UserController::class, 'store'])
            ->get(path: '/users/{id}', handler: [UserController::class, 'show']);

        // When: Getting routes
        $routes = $this->bootstrapper->getRoutes();

        // Then: Routes are registered
        $this->assertCount(expectedCount: 3, haystack: $routes);
    }

    /**
     * @test
     *
     * @throws ReflectionException
     * @throws ReservedRouteNameException
     */
    public function app_kernel_handles_complete_request_flow() : void
    {
        // Given: Complete application with routes and middleware
        $app = $this->bootstrapper
            ->globalMiddleware(middleware: [
                                               new CsrfVerificationMiddleware(responseFactory: $this->responseFactory),
                                           ])
            ->get(path: '/api/test', handler: static fn () => ['message' => 'API response'])
            ->createApp(dispatcher: $this->dispatcher, responseFactory: $this->responseFactory);

        // Mock router to return a simple response
        $this->router->method('resolve')->willReturnCallback(callback: function () {
            // Simulate controller execution
            return $this->responseFactory->response(data: ['message' => 'Router resolved']);
        });

        // When: Processing a request
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn(value: 'GET');
        $request->method('getAttribute')->with('_csrf_token')->willReturn(value: 'valid-token');

        $response = $app->handle(request: $request);

        // Then: Response is returned
        $this->assertInstanceOf(expected: ResponseInterface::class, actual: $response);
    }

    /**
     * @test
     *
     * @throws ReservedRouteNameException
     */
    public function middleware_groups_work_in_bootstrapper() : void
    {
        // Given: Middleware group defined
        $csrfMiddleware = new CsrfVerificationMiddleware(responseFactory: $this->responseFactory);

        $this->bootstrapper
            ->middlewareGroup(name: 'api', middleware: [$csrfMiddleware])
            ->useGroup(name: 'api')
            ->get(path: '/api/data', handler: static fn () => ['data' => 'test']);

        // When: Getting global middleware
        $globalMiddleware = $this->bootstrapper;

        // Then: Group middleware is applied
        $this->assertContains(needle: $csrfMiddleware, haystack: $globalMiddleware);
    }

    /**
     * @test
     *
     * @throws ReflectionException
     */
    public function route_grouping_preserves_middleware_stack() : void
    {
        // Given: Nested route groups
        $csrfMiddleware = new CsrfVerificationMiddleware(responseFactory: $this->responseFactory);

        $this->bootstrapper
            ->group(routes: static function ($router) use ($csrfMiddleware) : void {
                $router->middlewareGroup('secure', [$csrfMiddleware]);
                $router->useGroup('secure');

                $router->group(static function ($router) : void {
                    $router->get('/admin/users', [AdminController::class, 'users']);
                    $router->post('/admin/users', [AdminController::class, 'createUser']);
                });
            });

        // When: Creating app
        $app = $this->bootstrapper->createApp(dispatcher: $this->dispatcher, responseFactory: $this->responseFactory);

        // Then: App is created successfully
        $this->assertInstanceOf(expected: AppKernel::class, actual: $app);
    }

    /**
     * @test
     */
    public function app_kernel_provides_middleware_priority_hints() : void
    {
        // When: Getting priority hints
        $priorities = AppKernel::getMiddlewarePriorityHints();

        // Then: Priorities are provided
        $this->assertIsArray(actual: $priorities);
        $this->assertArrayHasKey(key: 'csrf', array: $priorities);
        $this->assertArrayHasKey(key: 'ip-restrict', array: $priorities);
        $this->assertArrayHasKey(key: 'cors', array: $priorities);
    }

    /**
     * @test
     */
    public function router_bootstrapper_validates_middleware_groups() : void
    {
        // Expect exception for undefined group
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(message: "Middleware group 'nonexistent' not defined");

        $this->bootstrapper->useGroup(name: 'nonexistent');
    }

    #[Override]
    protected function setUp() : void
    {
        $psr17Factory = new Psr17Factory;

        $this->router     = $this->createMock(RouterInterface::class);
        $routeCollection  = new RouteCollection;
        $this->dispatcher = new ControllerDispatcher(container: $this->createMock(ContainerInterface::class));
        $this->responseFactory = new Responses(
            streamFactory: $psr17Factory->createStreamFactory(),
            response     : $psr17Factory->createResponses()->createResponse(),
        );

        $this->bootstrapper = new RouterBootstrapper(router: $this->router, routeCollection: $routeCollection);
    }
}

// Mock controllers for testing
class UserController
{
    public function index() : array
    {
        return ['users' => []];
    }

    public function store() : array
    {
        return ['user' => ['id' => 1]];
    }

    public function show(int $id) : array
    {
        return ['user' => ['id' => $id]];
    }
}

class AdminController
{
    public function users() : array
    {
        return ['admin_users' => []];
    }

    public function createUser() : array
    {
        return ['admin_user' => ['id' => 1]];
    }
}
