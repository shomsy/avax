<?php

declare(strict_types=1);

namespace Avax\Container\Features\Operate\Boot;

use Avax\Container\Config\Settings;
use Avax\Container\Features\Core\ContainerBuilder;
use Avax\HTTP\Router\Kernel\RouterKernel;
use Avax\HTTP\Router\Router;
use Avax\HTTP\Router\Routing\HttpRequestRouter;
use Closure;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Fluent builder for composing and configuring web applications with dependency injection.
 *
 * This class provides a human-readable Domain Specific Language (DSL) for application bootstrap,
 * allowing developers to configure routing, middleware, exception handling, and container setup
 * through a chainable, declarative API. It serves as the primary entry point for application
 * initialization in the Avax framework.
 *
 * CONFIGURATION PHASES:
 * 1. Route registration (web and API endpoints)
 * 2. Middleware pipeline setup
 * 3. Exception handling configuration
 * 4. Container bootstrap and application assembly
 *
 * FLUENT API DESIGN:
 * The builder follows fluent interface patterns, enabling method chaining for readable configuration:
 * ```php
 * $app = (new ApplicationBuilder('/app'))
 *     ->exposeWeb('routes/web.php')
 *     ->exposeApi('routes/api.php')
 *     ->pipe($middleware)
 *     ->handle($exceptionHandler)
 *     ->build();
 * ```
 *
 * BOOTSTRAP PROCESS:
 * - Initializes the dependency injection container
 * - Loads route definitions from specified files
 * - Prepares middleware pipeline (currently stubbed for future implementation)
 * - Configures exception handling (currently stubbed for future implementation)
 * - Returns fully configured Application instance
 *
 * PERFORMANCE CONSIDERATIONS:
 * - Container bootstrap may involve expensive operations (reflection, caching)
 * - Route loading scans files and registers endpoints
 * - Middleware registration is deferred until build()
 * - Exception handler setup is deferred until build()
 *
 * THREAD SAFETY:
 * - Builder instances are not thread-safe
 * - Build process should be performed in single-threaded bootstrap phase
 * - Resulting Application instance may be thread-safe depending on configuration
 *
 * @package Avax\Container\Operate\Boot
 * @see     Application The resulting application instance
 * @see     ContainerBootstrapper For container initialization details
 * @see     docs_md/Features/Operate/Boot/ApplicationBuilder.md#quick-summary
 */
class ApplicationBuilder
{
    /** @var string|null Path to web routes file */
    private string|null $webRoutes = null;

    /** @var string|null Path to API routes file */
    private string|null $apiRoutes = null;

    /** @var callable[] Array of middleware callables for request pipeline */
    private array $middleware = [];

    /** @var Closure|null Global exception handler callback */
    private Closure|null $exceptionsCallback = null;

    /** @var ContainerBuilder */
    private ContainerBuilder $containerBuilder;
    /**
     * @var string[]|ServiceProvider[]|Closure[] Array of providers to register
     */
    private array $providers = [];

    /**
     * Creates a new ApplicationBuilder instance for the specified base path.
     *
     * Initializes the builder with the application's root directory.
     * The base path is used for resolving relative file paths and cache directories during the build process.
     *
     * @param string $basePath Absolute path to the application root directory
     *
     * @see docs_md/Features/Operate/Boot/ApplicationBuilder.md#method-__construct
     */
    public function __construct(
        private readonly string $basePath
    ) {
        $this->containerBuilder = ContainerBuilder::create();
        $this->containerBuilder->cacheDir(dir: $basePath . '/var/cache');
    }

    /**
     * Specifies the file containing web route definitions.
     *
     * registers a route file that defines HTTP endpoints for web interface.
     * The file path is relative to the application base path and will be
     * loaded during application bootstrap.
     *
     * ROUTE FILE FORMAT:
     * ```php
     * // routes/web.php
     * $app->get('/home', [HomeController::class, 'index']);
     * $app->post('/login', [AuthController::class, 'login']);
     * ```
     *
     * @param string $path Relative path to web routes file from application base
     *
     * @return $this For method chaining
     * @see docs_md/Features/Operate/Boot/ApplicationBuilder.md#method-exposeweb
     */
    public function exposeWeb(string $path): self
    {
        $this->webRoutes = $path;

        return $this;
    }

    /**
     * Specifies the file containing API route definitions.
     *
     * Registers a route file that defines REST API endpoints. Similar to web routes
     * but typically used for JSON-based API responses and stateless operations.
     *
     * API ROUTE EXAMPLE:
     * ```php
     * // routes/api.php
     * $app->get('/api/users', [UserApiController::class, 'index']);
     * $app->post('/api/users', [UserApiController::class, 'store']);
     * ```
     *
     * @param string $path Relative path to API routes file from application base
     *
     * @return $this For method chaining
     * @see docs_md/Features/Operate/Boot/ApplicationBuilder.md#method-exposeapi
     */
    public function exposeApi(string $path): self
    {
        $this->apiRoutes = $path;

        return $this;
    }

    /**
     * Adds middleware to the global request processing pipeline.
     *
     * Registers a middleware callable that will wrap all incoming requests.
     * Middleware is executed in registration order, allowing for layered
     * request processing (authentication, logging, CORS, etc.).
     *
     * MIDDLEWARE SIGNATURE:
     * ```php
     * function middleware(Request $request, callable $next): Response {
     *     // Pre-processing
     *     $response = $next($request);
     *     // Post-processing
     *     return $response;
     * }
     * ```
     *
     * USAGE:
     * ```php
     * $builder->pipe(new AuthenticationMiddleware())
     *         ->pipe(new LoggingMiddleware());
     * ```
     *
     * @param callable $middleware Middleware callable for request processing
     *
     * @return $this For method chaining
     * @see docs_md/Features/Operate/Boot/ApplicationBuilder.md#method-pipe
     */
    public function pipe(callable $middleware): self
    {
        $this->middleware[] = $middleware;

        return $this;
    }

    /**
     * Registers the global exception handler for uncaught exceptions.
     *
     * Sets up a callback that will handle any uncaught exceptions during
     * request processing. The handler receives the exception and request
     * context for appropriate error responses.
     *
     * HANDLER SIGNATURE:
     * ```php
     * function handleException(Throwable $exception, Request $request): Response {
     *     if ($exception instanceof ValidationException) {
     *         return new JsonResponse(['error' => 'Invalid input'], 422);
     *     }
     *     return new JsonResponse(['error' => 'Internal server error'], 500);
     * }
     * ```
     *
     * @param callable $handler Exception handler callable
     *
     * @return $this For method chaining
     * @see docs_md/Features/Operate/Boot/ApplicationBuilder.md#method-handle
     */
    public function handle(callable $handler): self
    {
        $this->exceptionsCallback = $handler instanceof Closure ? $handler : $handler(...);

        return $this;
    }

    /**
     * Register service providers to be loaded during application build.
     *
     * @param array $providers List of provider classes or instances
     *
     * @return self
     * @see docs_md/Features/Operate/Boot/ApplicationBuilder.md#method-withproviders
     */
    public function withProviders(array $providers): self
    {
        $this->providers = array_merge($this->providers, $providers);

        return $this;
    }

    /**
     * Assembles and returns the fully configured application instance.
     *
     * This method performs the actual application bootstrap by:
     * 1. Initializing the dependency injection container
     * 2. Loading route definitions from registered files
     * 3. Preparing middleware and exception handling (deferred implementation)
     * 4. Registering and booting service providers
     * 5. Returning the ready-to-use Application instance
     *
     * BOOTSTRAP SEQUENCE:
     * ```php
     * 1. Container Bootstrap -> 2. Route Loading -> 3. Middleware Setup -> 4. Provider Registration -> 5. Return App
     * ```
     *
     * PERFORMANCE IMPACT:
     * - Container bootstrap may load cached definitions or perform reflection
     * - Route loading scans files and registers endpoints
     * - This method should be called once during application startup
     *
     * @return Application Fully configured and bootstrapped application instance
     * @throws \Avax\Container\Features\Core\Exceptions\ContainerExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @see docs_md/Features/Operate/Boot/ApplicationBuilder.md#method-build
     */
    public function build(): Application
    {
        // Core HTTP Components binding
        $this->containerBuilder->singleton(abstract: HttpRequestRouter::class);
        $this->containerBuilder->singleton(abstract: RouterKernel::class);
        $this->containerBuilder->singleton(abstract: Router::class);

        // Bind aliases for core components
        $this->containerBuilder->singleton(abstract: 'router', concrete: function ($c) {
            return $c->get(Router::class);
        });

        // Bind config abstract to concrete
        $this->containerBuilder->singleton(abstract: 'config', concrete: Settings::class);
        $this->containerBuilder->singleton(abstract: Settings::class, concrete: Settings::class);

        $container = $this->containerBuilder->build();

        $app = new Application(
            basePath: $this->basePath,
            container: $container
        );

        // Register Core Framework Providers FIRST
        $this->registerCoreProviders(app: $app);

        // Register manually configured providers
        foreach ($this->providers as $provider) {
            $app->register(provider: $provider);
        }

        if ($this->webRoutes !== null && $this->webRoutes !== '' && $this->webRoutes !== '0') {
            $app->loadRoutes(path: $this->webRoutes);
        }

        if ($this->apiRoutes !== null && $this->apiRoutes !== '' && $this->apiRoutes !== '0') {
            $app->loadRoutes(path: $this->apiRoutes);
        }

        foreach ($this->middleware as $middleware) {
            // Explicitly reference middleware for future wiring.
            assert(is_callable($middleware));
        }

        if ($this->exceptionsCallback instanceof Closure) {
            $callback = $this->exceptionsCallback;
            assert(is_callable($callback));
        }

        // TODO: Register Middleware ($this->middleware)
        // TODO: Register Exception Handler ($this->exceptionsCallback)

        // Boot the application (boots all registered providers)
        $app->boot();

        return $app;
    }

    /**
     * Automatically discover and register core framework service providers.
     *
     * Scans the Foundation/Container/Providers directory for ServiceProvider classes
     * and registers them to ensure core functionality (Auth, DB, HTTP, etc.) is available.
     *
     * @param Application $app The application instance
     *
     * @return void
     * @throws \Avax\Container\Features\Core\Exceptions\ContainerExceptionInterface
     * @see docs_md/Features/Operate/Boot/ApplicationBuilder.md#method-registercoreproviders
     */
    private function registerCoreProviders(Application $app): void
    {
        // Path to Foundation/Container/Providers
        // Current file is in Foundation/Container/Features/Operate/Boot
        // dirname(__DIR__, 3) takes us to Foundation/Container
        $providersPath = dirname(__DIR__, 3) . '/Providers';

        if (! is_dir($providersPath)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            iterator: new RecursiveDirectoryIterator(directory: $providersPath, flags: RecursiveDirectoryIterator::SKIP_DOTS),
            mode: RecursiveIteratorIterator::SELF_FIRST
        );

        /** @var \SplFileInfo $item */
        foreach ($iterator as $item) {
            if ($item->isFile() && $item->getExtension() === 'php') {
                // Calculate relative class path
                $relativePath = substr($item->getPathname(), strlen($providersPath) + 1);

                // Convert to namespace
                $className = 'Avax\\Container\\Providers\\' . str_replace(['/', '.php'], ['\\', ''], $relativePath);

                if (class_exists($className) && is_subclass_of($className, ServiceProvider::class)) {
                    $app->register(provider: $className);
                }
            }
        }
    }
}
