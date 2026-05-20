<?php

declare(strict_types=1);

namespace Avax\Framework\System\PublicSurface;

use Avax\Components\HTTP\Request\System\Flows\CreateRequestFromGlobals\CreateRequestFromGlobals;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteMethod;
use Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse\CreateHttpResponse;
use Avax\Framework\System\Capabilities\PreCommit\Configuration\PreCommitConfig;
use Avax\Framework\System\Capabilities\PreCommit\PreCommit;
use Avax\Framework\System\Capabilities\Runtime\RuntimeInterface;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Capabilities\Runtime\RuntimeResponse;
use Avax\Framework\System\Capabilities\Runtime\RuntimeResult;
use Avax\Framework\System\Capabilities\StateReset\StateResetReport;
use Avax\Framework\System\Flows\HandleIncomingHttp\CloseHttpRequestScope;
use Avax\Framework\System\Flows\HandleIncomingHttp\CreateRuntimeRequestFromHttpRequest;
use Avax\Framework\System\Flows\HandleIncomingHttp\FrameworkRouteRegistrar;
use Avax\Framework\System\Flows\HandleIncomingHttp\OpenHttpRequestScope;
use Avax\Framework\System\Flows\ResetApplicationState\ResetApplicationState;
use Avax\Framework\System\Flows\RunApplication\RunApplication;
use Avax\Framework\System\Flows\RunConsoleCommand\RunConsoleCommand;
use Avax\Framework\System\Foundation\Environment\EnvironmentName;
use Avax\Framework\System\PublicSurface\Console\ConsoleKernel;
use Avax\Framework\System\PublicSurface\Console\ConsoleKernelInterface;
use Avax\Framework\System\PublicSurface\Http\HttpKernelInterface;
use Avax\Framework\System\PublicSurface\Runtime\RuntimeKernel;
use Avax\Framework\System\PublicSurface\Runtime\RuntimeKernelInterface;
use Closure;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;

/**
 * App — V4 zero-configuration runnable App API.
 *
 * Target DX:
 *   $app = Avax::create();
 *   $app->get('/', fn () => 'Hello AvaX');
 *   $app->post('/register', RegisterUser::class);
 *   $app->run();
 *
 * The user must not need to manually touch:
 * - Router
 * - Container
 * - Middleware pipeline
 * - Request factory
 * - Response factory
 * - DataTransfer engine
 * - SecureRequest resolver
 *
 * Internally, App delegates to existing AvaX components:
 * - Route registration uses RouteCollection / RouteDefinition
 * - Controller invocation uses RunApplication with normalized results
 * - Response normalization via NormalizeControllerResult
 * - Request scope uses OpenHttpRequestScope / CloseHttpRequestScope
 * - Error handling via ClassifyApplicationException / RenderApplicationError
 */
final class App
{
    /**
     * @var list<Closure>
     */
    private array $globalMiddleware = [];

    private bool $running = false;

    private Closure|null $exceptionHandler = null;

    public function __construct(
        private readonly RuntimeInterface $runtime,
        private readonly ResetApplicationState $resetApplicationState,
        private readonly CreateHttpResponse $createHttpResponse,
        private readonly CreateRequestFromGlobals $createRequestFromGlobals,
        private readonly RunApplication $dispatcher,
        private readonly FrameworkRouteRegistrar $routeRegistrar,
        private readonly OpenHttpRequestScope $openRequestScope,
        private readonly CloseHttpRequestScope $closeRequestScope,
        private readonly CreateRuntimeRequestFromHttpRequest $createRuntimeRequest,
    ) {
    }

    /**
     * Register a GET route.
     */
    public function get(string $path, mixed $action): self
    {
        $this->registerRoute(method: RouteMethod::GET, path: $path, action: $action);

        return $this;
    }

    /**
     * Register a POST route.
     */
    public function post(string $path, mixed $action): self
    {
        $this->registerRoute(method: RouteMethod::POST, path: $path, action: $action);

        return $this;
    }

    /**
     * Register a PUT route.
     */
    public function put(string $path, mixed $action): self
    {
        $this->registerRoute(method: RouteMethod::PUT, path: $path, action: $action);

        return $this;
    }

    /**
     * Register a PATCH route.
     */
    public function patch(string $path, mixed $action): self
    {
        $this->registerRoute(method: RouteMethod::PATCH, path: $path, action: $action);

        return $this;
    }

    /**
     * Register a DELETE route.
     */
    public function delete(string $path, mixed $action): self
    {
        $this->registerRoute(method: RouteMethod::DELETE, path: $path, action: $action);

        return $this;
    }

    /**
     * Register a route for all HTTP methods.
     */
    public function any(string $path, mixed $action): self
    {
        $this->routeRegistrar->anyExpanded(path: $path, action: $action);

        return $this;
    }

    /**
     * Register global middleware.
     *
     * Middleware receives (ServerRequestInterface, callable $next): ResponseInterface.
     */
    public function use(Closure $middleware): self
    {
        $this->globalMiddleware[] = $middleware;

        return $this;
    }

    /**
     * Register a custom exception handler.
     *
     * Handler receives (Throwable $exception): ResponseInterface.
     */
    public function exceptionHandler(Closure $handler): self
    {
        $this->exceptionHandler = $handler;

        return $this;
    }

    /**
     * Run the application.
     *
     * Builds a RuntimeRequest from PHP superglobals, matches routes,
     * dispatches through the controller pipeline, and returns the response.
     *
     * @throws RuntimeException When app is already running
     */
    public function run(): ResponseInterface
    {
        if ($this->running) {
            throw new RuntimeException('App is already running.');
        }

        $this->running = true;

        try {
            return $this->handleRequest();
        } finally {
            $this->running = false;
        }
    }

    /**
     * Handle a RuntimeRequest directly (useful for testing).
     */
    public function handle(RuntimeRequest $request): ResponseInterface
    {
        $this->openRequestScope->open(request: $request);

        try {
            $response = $this->dispatcher->handle(
                runtimeRequest: $request,
                routes: $this->routeRegistrar->collectRoutes(),
            );

            $this->runtime->context()->finishRequest(
                runtimeResult: RuntimeResult::fromResponse(runtimeResponse: RuntimeResponse::fromPsrResponse(response: $response)),
            );

            return $response;
        } catch (Throwable $e) {
            return $this->handleException($e);
        } finally {
            $this->closeRequestScope->close();
        }
    }

    /**
     * Access the underlying runtime for advanced use.
     */
    public function runtime(): RuntimeInterface
    {
        return $this->runtime;
    }

    /**
     * Reset application state.
     */
    public function resetState(): StateResetReport
    {
        return $this->resetApplicationState->reset();
    }

    /**
     * Provide an HttpKernel interface for compatibility with existing kernels.
     */
    public function asHttpKernel(): HttpKernelInterface
    {
        return new class($this) implements HttpKernelInterface {
            public function __construct(private App $app) {}

            public function handle(RuntimeRequest $request): RuntimeResponse
            {
                $response = $this->app->handle($request);

                return RuntimeResponse::fromPsrResponse(response: $response);
            }
        };
    }

    /**
     * Provide a ConsoleKernel interface for compatibility.
     */
    public function asConsoleKernel(): ConsoleKernel
    {
        return new ConsoleKernel(
            runConsoleCommand: new RunConsoleCommand(
                runtime: $this->runtime,
                preCommitConfig: new PreCommitConfig(),
                preCommit: new PreCommit(preCommitConfig: new PreCommitConfig()),
            ),
        );
    }

    /**
     * Provide a RuntimeKernel interface for compatibility.
     */
    public function asRuntimeKernel(): RuntimeKernel
    {
        return new RuntimeKernel(runtime: $this->runtime);
    }

    private function registerRoute(RouteMethod $method, string $path, mixed $action): void
    {
        match ($method) {
            RouteMethod::GET => $this->routeRegistrar->get(path: $path, action: $action),
            RouteMethod::POST => $this->routeRegistrar->post(path: $path, action: $action),
            RouteMethod::PUT => $this->routeRegistrar->put(path: $path, action: $action),
            RouteMethod::PATCH => $this->routeRegistrar->patch(path: $path, action: $action),
            RouteMethod::DELETE => $this->routeRegistrar->delete(path: $path, action: $action),
            RouteMethod::OPTIONS => $this->routeRegistrar->options(path: $path, action: $action),
            RouteMethod::HEAD => $this->routeRegistrar->head(path: $path, action: $action),
        };
    }

    private function handleRequest(): ResponseInterface
    {
        // V5-13: Delegate superglobal access to canonical Request component.
        // App.php must not access $_SERVER/$_GET/$_POST/$_FILES/php://input directly.
        $avaxRequest = $this->createRequestFromGlobals->execute();

        $request = $this->createRuntimeRequest->create(request: $avaxRequest);

        return $this->handle(request: $request);
    }

    private function handleException(Throwable $e): ResponseInterface
    {
        if ($this->exceptionHandler instanceof Closure) {
            try {
                return ($this->exceptionHandler)($e);
            } catch (Throwable) {
                // Fall through to default handler
            }
        }

        $isProduction = $this->runtime->environment()->isProduction();
        $message = $isProduction ? 'Internal server error' : $e->getMessage();
        $status = $isProduction ? 500 : 500;

        return $this->createHttpResponse->error(
            message: $message,
            status : $status,
        );
    }
}
