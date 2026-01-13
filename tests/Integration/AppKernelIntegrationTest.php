<?php

declare(strict_types=1);

use Avax\HTTP\AppKernel;
use Avax\HTTP\Dispatcher\ControllerDispatcher;
use Avax\HTTP\Middleware\CsrfVerificationMiddleware;
use Avax\HTTP\Response\ResponseFactory;
use Avax\HTTP\Router\RouteCollection;
use Avax\HTTP\Router\RouterInterface;
use Avax\HTTP\Router\Routing\RouteCollection;
use Avax\HTTP\RouterBootstrapper;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
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

    private RouteCollection $routeCollection;

    private ControllerDispatcher $dispatcher;

    private ResponseFactory $responseFactory;

    private RouterBootstrapper $bootstrapper;

    /**
     * @test
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
     */
    public function app_kernel_handles_complete_request_flow() : void
    {
        // Given: Complete application with routes and middleware
        $app = $this->bootstrapper
            ->globalMiddleware(middleware: [
                new CsrfVerificationMiddleware(responseFactory: $this->responseFactory),
            ])
            ->get(path: '/api/test', handler: static function () {
                return ['message' => 'API response'];
            })
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
     */
    public function middleware_groups_work_in_bootstrapper() : void
    {
        // Given: Middleware group defined
        $csrfMiddleware = new CsrfVerificationMiddleware(responseFactory: $this->responseFactory);

        $this->bootstrapper
            ->middlewareGroup(name: 'api', middleware: [$csrfMiddleware])
            ->useGroup(name: 'api')
            ->get(path: '/api/data', handler: static function () {
                return ['data' => 'test'];
            });

        // When: Getting global middleware
        $globalMiddleware = $this->bootstrapper->getGlobalMiddleware();

        // Then: Group middleware is applied
        $this->assertContains(needle: $csrfMiddleware, haystack: $globalMiddleware);
    }

    /**
     * @test
     */
    public function route_grouping_preserves_middleware_stack() : void
    {
        // Given: Nested route groups
        $csrfMiddleware = new CsrfVerificationMiddleware(responseFactory: $this->responseFactory);

        $this->bootstrapper
            ->group(routes: static function ($router) use ($csrfMiddleware) {
                $router->middlewareGroup('secure', [$csrfMiddleware]);
                $router->useGroup('secure');

                $router->group(static function ($router) {
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

    protected function setUp() : void
    {
        $psr17Factory = new Psr17Factory;

        $this->router          = $this->createMock(RouterInterface::class);
        $this->routeCollection = new RouteCollection;
        $this->dispatcher      = new ControllerDispatcher(container: $this->createMock(ContainerInterface::class));
        $this->responseFactory = new ResponseFactory(
            streamFactory: $psr17Factory->createStreamFactory(),
            response     : $psr17Factory->createResponseFactory()->createResponse()
        );

        $this->bootstrapper = new RouterBootstrapper(router: $this->router, routeCollection: $this->routeCollection);
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
