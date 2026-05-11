<?php

declare(strict_types=1);

namespace Avax\Framework\System\PublicSurface;

use Avax\Components\HTTP\Request\System\Capabilities\Body\ParseFormBody;
use Avax\Components\HTTP\Request\System\Capabilities\Body\ParseJsonBody;
use Avax\Components\HTTP\Request\System\Capabilities\Files\NormalizeUploadedFiles;
use Avax\Components\HTTP\Request\System\Capabilities\Headers\NormalizeHeaders;
use Avax\Components\HTTP\Request\System\Flows\CreateRequestFromGlobals\CreateRequestFromGlobals;
use Avax\Components\HTTP\Request\System\Flows\CreateRequestFromGlobals\ReadQueryParameters;
use Avax\Components\HTTP\Request\System\Flows\CreateRequestFromGlobals\ReadRequestBody;
use Avax\Components\HTTP\Request\System\Flows\CreateRequestFromGlobals\ReadServerParameters;
use Avax\Components\HTTP\Request\System\Flows\CreateRequestFromGlobals\ReadUploadedFiles;
use Avax\Components\HTTP\Response\ResponseFactory;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteMethod;
use Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Avax\Components\Operations\Observability\System\Capabilities\MetricsCollector\MetricsCollector;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\RenderApplicationError\RenderApplicationError;
use Avax\Framework\System\Capabilities\Runtime\RuntimeInterface;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Capabilities\Runtime\RuntimeResponse;
use Avax\Framework\System\Capabilities\Runtime\RuntimeResult;
use Avax\Framework\System\Capabilities\StateReset\StateResetReport;
use Avax\Framework\System\Flows\HandleIncomingHttp\CloseHttpRequestScope;
use Avax\Framework\System\Flows\HandleIncomingHttp\OpenHttpRequestScope;
use Avax\Framework\System\Flows\HandleIncomingHttp\RegisteredHttpRoutes;
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
     * @var list<RouteDefinition>
     */
    private array $routeDefinitions = [];

    /**
     * @var list<Closure>
     */
    private array $globalMiddleware = [];

    private bool $running = false;

    private Closure|null $exceptionHandler = null;

    private RunApplication|null $dispatcher = null;

    private ResponseFactory|null $responseFactory = null;

    public function __construct(
        private readonly RuntimeInterface $runtime,
        private readonly ResetApplicationState $resetApplicationState,
        private readonly ?MetricsCollector $metricsCollector = null,
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
        foreach (RouteMethod::cases() as $method) {
            $this->registerRoute(method: $method, path: $path, action: $action);
        }

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
        $this->ensureInitialized();
        assert($this->dispatcher !== null);

        $openScope = new OpenHttpRequestScope(
            requestScopes: $this->runtime->requestScopes(),
            runtimeContext: $this->runtime->context(),
        );
        $closeScope = new CloseHttpRequestScope(requestScopes: $this->runtime->requestScopes());

        $openScope->open(request: $request);

        try {
            $response = $this->dispatcher->handle(
                runtimeRequest: $request,
                routes: $this->buildRegisteredRoutes(),
            );

            $this->runtime->context()->finishRequest(
                runtimeResult: RuntimeResult::fromResponse(runtimeResponse: RuntimeResponse::fromPsrResponse(response: $response)),
            );

            return $response;
        } catch (Throwable $e) {
            return $this->handleException($e);
        } finally {
            $closeScope->close();
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
            runConsoleCommand: new RunConsoleCommand($this->runtime),
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
        $definition = new RouteDefinition(
            method: $method,
            uri: $path,
            action: $action,
        );

        $this->routeDefinitions[] = $definition;
    }

    private function ensureInitialized(): void
    {
        if ($this->dispatcher === null) {
            $this->dispatcher = new RunApplication(metricsCollector: $this->metricsCollector);
            $this->responseFactory = new ResponseFactory();
        }
    }

    private function buildRegisteredRoutes(): RegisteredHttpRoutes
    {
        $routesByMethod = [];

        foreach ($this->routeDefinitions as $definition) {
            $method = strtoupper($definition->method()->value);
            $routesByMethod[$method] ??= [];
            $routesByMethod[$method][] = $definition;
        }

        return new RegisteredHttpRoutes(
            routesByMethod: $routesByMethod,
            fallback: null,
        );
    }

    private function handleRequest(): ResponseInterface
    {
        $this->ensureInitialized();

        // V5-13: Delegate superglobal access to canonical Request component.
        // App.php must not access $_SERVER/$_GET/$_POST/$_FILES/php://input directly.
        $createRequest = new CreateRequestFromGlobals(
            readServerParameters: new ReadServerParameters(),
            readQueryParameters: new ReadQueryParameters(),
            readUploadedFiles: new ReadUploadedFiles(),
            readRequestBody: new ReadRequestBody(),
            normalizeHeaders: new NormalizeHeaders(),
            normalizeUploadedFiles: new NormalizeUploadedFiles(),
            parseJsonBody: new ParseJsonBody(),
            parseFormBody: new ParseFormBody(),
        );

        $avaxRequest = $createRequest->execute();

        /** @var array<string, list<string>> $headers */
        $headers = $avaxRequest->getHeaders();

        $request = new RuntimeRequest(
            method: $avaxRequest->getMethod(),
            uri: (string) $avaxRequest->getUri(),
            headers: $headers,
            body: (string) $avaxRequest->getBody(),
        );

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

        $renderer = new RenderApplicationError(
            responseFactory: $this->responseFactory ?? new ResponseFactory(),
        );

        $isProduction = $this->runtime->environment()->isProduction();

        return $renderer->render(e: $e, isProduction: $isProduction);
    }
}
