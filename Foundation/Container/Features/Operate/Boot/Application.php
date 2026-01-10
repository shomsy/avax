<?php

declare(strict_types=1);

namespace Avax\Container\Features\Operate\Boot;

use Avax\Config\Architecture\DDD\AppPath;
use Avax\Container\Config\Settings;
use Avax\Container\Features\Core\Contracts\BindingBuilder;
use Avax\Container\Features\Core\Contracts\ContainerInternalInterface;
use Avax\Container\Features\Define\Bind\Registrar;
use Avax\HTTP\Request\Request;
use Avax\HTTP\Response\Classes\Response;
use Avax\HTTP\Router\Cache\RouteCacheLoader;
use Avax\HTTP\Router\Router;
use Avax\HTTP\Router\Routing\HttpRequestRouter;
use Avax\HTTP\Router\Support\RouteCollector;

/** @noinspection PhpInternalEntityUsedInspection */

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

require_once dirname(__DIR__, 4) . '/Helpers/app_instance.php';

/**
 * HTTP Application Kernel with Container Integration.
 *
 * This class serves as the main entry point for web applications, combining
 * dependency injection container functionality with HTTP request handling,
 * routing, and service provider management.
 *
 * KEY FEATURES:
 * - HTTP Request/Response lifecycle management
 * - Route loading and caching with container integration
 * - Service provider system for modular application structure
 * - Configuration loading from files
 * - PSR-11 container delegation
 *
 * APPLICATION LIFECYCLE:
 * 1. Construction: Base bindings and config loading
 * 2. Route loading: Dynamic or cached route registration
 * 3. Provider registration: Modular service registration
 * 4. Boot phase: Provider initialization
 * 5. Request handling: HTTP request processing
 * 6. Termination: Cleanup and scope ending
 *
 * USAGE EXAMPLE:
 * ```php
 * // Create application
 * $app = Application::start('/path/to/app');
 *
 * // Register service provider
 * $app->register(DatabaseServiceProvider::class);
 *
 * // Load routes
 * $app->loadRoutes('/path/to/routes.php');
 *
 * // Handle HTTP request
 * $response = $app->run();
 * ```
 *
 * ROUTE LOADING:
 * Routes are loaded with container-aware dependency injection:
 * ```php
 * // routes.php
 * $router->get('/users', function(UserRepository $users) {
 *     return $users->getAll(); // Auto-injected by container
 * });
 * ```
 *
 * SERVICE PROVIDERS:
 * Enable modular application architecture:
 * ```php
 * class DatabaseServiceProvider extends ServiceProvider
 * {
 *     public function register(): void
 *     {
 *         $this->app->singleton(Database::class, PDO::class);
 *     }
 * }
 * ```
 *
 * @see ContainerInterface For container functionality
 * @see ServiceProvider For modular service registration
 * @see ApplicationBuilder For fluent application construction
 */
class Application implements ContainerInterface
{
    /** @var ServiceProvider[] */
    private array $providers = [];

    private bool $booted = false;

    private bool $routeCacheLoaded = false;

    /** @var array<string, bool> */
    private array $loadedRouteFiles = [];

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @see docs/Features/Operate/Boot/Application.md#method-__construct
     */
    public function __construct(
        public readonly string                      $basePath,
        private readonly ContainerInternalInterface $container
    ) {
        $this->registerBaseBindings();
        $this->container->instance(abstract: self::class, instance: $this);
        $this->container->instance(abstract: 'app', instance: $this);

        $this->log(message: "Application initialized with base path: {$this->basePath}");
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function registerBaseBindings(): void
    {
        $containerSettings = new Settings(items: []);
        $this->loadConfig(containerSettings: $containerSettings);

        $this->container->instance(abstract: Settings::class, instance: $containerSettings);
        $this->container->instance(abstract: 'config', instance: $containerSettings);
        $this->container->instance(abstract: ContainerInternalInterface::class, instance: $this->container);

        $this->log(message: "Base bindings registered.");
    }

    private function loadConfig(Settings $containerSettings): void
    {
        $configDir = $this->basePath . '/Config';
        if (! is_dir($configDir)) {
            return;
        }

        foreach (glob($configDir . '/*.php') as $file) {
            $key  = pathinfo($file, PATHINFO_FILENAME);
            $data = require $file;

            if (! is_array($data)) {
                continue;
            }

            $containerSettings->set(key: $key, value: $data);
        }
    }

    /**
     * @see docs/Features/Operate/Boot/Application.md#method-instance
     */
    public function instance(string $abstract, object $instance): void
    {
        $this->container->instance(abstract: $abstract, instance: $instance);
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function log(string $message, string $level = 'info'): void
    {
        if ($this->container->has(id: 'Psr\Log\LoggerInterface')) {
            $this->container->get(id: 'Psr\Log\LoggerInterface')->log($level, $message);
        }
    }

    /**
     * Proxy check to the kernel container.
     *
     * @param string $id Service identifier to check
     *
     * @return bool True if service is registered or can be resolved
     * @see docs/Features/Operate/Boot/Application.md#method-has
     */
    public function has(string $id): bool
    {
        return $this->container->has(id: $id);
    }

    /**
     * Proxy resolution to the underlying container.
     *
     * @see docs/Features/Operate/Boot/Application.md#method-get
     */
    public function get(string $id)
    {
        return $this->container->get(id: $id);
    }

    /**
     * Create an application builder for the given root path.
     *
     * This is the recommended way to create Application instances.
     * Provides fluent configuration API for setting up your application.
     *
     * @param string $root The application root directory path
     *
     * @return ApplicationBuilder Fluent builder for application configuration
     * @see docs/Features/Operate/Boot/Application.md#method-start
     */
    public static function start(string $root): ApplicationBuilder
    {
        return new ApplicationBuilder(basePath: $root);
    }

    /**
     * @see docs/Features/Operate/Boot/Application.md#method-bind
     */
    public function bind(string $abstract, mixed $concrete = null): BindingBuilder
    {
        return (new Registrar($this->container->getDefinitions()))
            ->bind($abstract, $concrete);
    }

    // PSR-11 Implementation via Delegation

    /**
     * @see docs/Features/Operate/Boot/Application.md#method-singleton
     */
    public function singleton(string $abstract, mixed $concrete = null): BindingBuilder
    {
        return (new Registrar($this->container->getDefinitions()))
            ->singleton($abstract, $concrete);
    }

    /**
     * @see docs/Features/Operate/Boot/Application.md#method-scoped
     */
    public function scoped(string $abstract, mixed $concrete = null): BindingBuilder
    {
        return (new Registrar($this->container->getDefinitions()))
            ->scoped($abstract, $concrete);
    }

    /**
     * @see docs/Features/Operate/Boot/Application.md#method-make
     */
    public function make(string $abstract, array $parameters = []): object
    {
        return $this->container->make(abstract: $abstract, parameters: $parameters);
    }

    /**
     * Retrieve the underlying kernel container.
     *
     * Provides direct access to the container for advanced usage.
     * Most operations should be done through the Application interface.
     *
     * @return CoreContainerInterface The underlying dependency injection container
     * @see docs/Features/Operate/Boot/Application.md#method-getcontainer
     */
    public function getContainer(): ContainerInternalInterface
    {
        return $this->container;
    }

    /**
     * Load routes from the provided file path and register them with the router.
     *
     * @see docs/Features/Operate/Boot/Application.md#method-loadroutes
     */
    public function loadRoutes(string $path): void
    {
        if ($this->routeCacheLoaded) {
            $this->loadedRouteFiles[$path] = true;
            $this->log(message: "Routes already loaded from cache, skipping file: $path");

            return;
        }

        if (isset($this->loadedRouteFiles[$path]) || ! file_exists($path)) {
            return;
        }

        $router      = $this->container->get(id: Router::class);
        $httpRouter  = $this->container->get(id: HttpRequestRouter::class);
        $cachePath   = AppPath::ROUTE_CACHE_PATH->get();
        $cacheLoader = new RouteCacheLoader(router: $router);
        $cacheExists = is_file($cachePath) && is_readable($cachePath);

        if ($cacheExists) {
            try {
                $cacheLoader->load(cachePath: $cachePath);
                $this->routeCacheLoaded        = true;
                $this->loadedRouteFiles[$path] = true;
                $this->log(message: "Loaded routes from cache: $cachePath");

                return;
            } catch (RuntimeException) {
                $cacheExists = false;
                $this->log(message: "Failed to load routes from cache: $cachePath", level: 'warning');
            }
        }

        RouteCollector::reset();

        require $path;

        foreach (RouteCollector::flushBuffered() as $routeBuilder) {
            $httpRouter->registerRoute(
                method: $routeBuilder->method,
                path: $routeBuilder->path,
                action: $routeBuilder->action,
                middleware: $routeBuilder->middleware,
                name: $routeBuilder->name,
                constraints: $routeBuilder->constraints,
                defaults: $routeBuilder->defaults,
                domain: $routeBuilder->domain,
                attributes: $routeBuilder->attributes,
                authorization: $routeBuilder->authorization,
            );
        }

        if ($fallback = RouteCollector::getFallback()) {
            $httpRouter->fallback(handler: $fallback);
        }

        try {
            $cacheLoader->write(cachePath: $cachePath);
            $this->log(message: "Routes cached to: $cachePath");
        } catch (RuntimeException) {
            // Cache writing failure should not interrupt request handling.
            $this->log(message: "Failed to write routes cache: $cachePath", level: 'warning');
        }

        $this->loadedRouteFiles[$path] = true;
        $this->log(message: "Loaded routes from file: $path");
    }

    /**
     * Register a service provider with the application.
     *
     *
     * @see docs/Features/Operate/Boot/Application.md#method-register
     */
    public function register(string|ServiceProvider $provider): ServiceProvider
    {
        if (is_string($provider)) {
            $provider = new $provider($this);
        }

        if (array_key_exists($provider::class, $this->providers)) {
            return $this->providers[$provider::class];
        }

        $this->log(message: "Registering service provider: " . $provider::class);

        $this->providers[$provider::class] = $provider;

        if (method_exists($provider, 'register')) {
            $provider->register();
        }

        if ($this->booted) {
            $this->bootProvider(provider: $provider);
        }

        return $provider;
    }

    /**
     * @param \Avax\Container\Features\Operate\Boot\ServiceProvider $provider
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function bootProvider(ServiceProvider $provider): void
    {
        if (method_exists($provider, 'boot')) {
            $this->log(message: "Booting service provider: " . $provider::class);
            $this->container->call(callable: [$provider, 'boot']);
        }
    }

    /**
     * Run the application lifecycle.
     * Boot providers, hand off to the HTTP layer (placeholder), and terminate.
     *
     * @see docs/Features/Operate/Boot/Application.md#method-call
     */
    public function call(callable|string $callable, array $parameters = []): mixed
    {
        return $this->container->call(callable: $callable, parameters: $parameters);
    }

    /**
     * Boot the application, dispatch the HTTP request, and terminate.
     *
     * @see docs/Features/Operate/Boot/Application.md#method-run
     */
    public function run(): ResponseInterface
    {
        $this->log(message: "Application run started.");
        $this->container->beginScope();

        try {
            $this->boot();
            $request = Request::createFromGlobals();
            $this->container->instance(abstract: Request::class, instance: $request);

            $router   = $this->container->get(id: Router::class);
            $response = $router->resolve($request);

            $this->sendResponse(response: $response);

            $this->log(message: "Application run finished.");

            return $response;
        } finally {
            $this->terminate();
        }
    }

    /**
     * Boot all registered service providers.
     *
     * @throws \Avax\Container\Features\Core\Exceptions\ContainerExceptionInterface
     * @see docs/Features/Operate/Boot/Application.md#method-boot
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        foreach ($this->providers as $provider) {
            $this->bootProvider(provider: $provider);
        }

        $this->booted = true;
    }

    /**
     * Send the response using available transport helpers.
     *
     * Handles different response types appropriately:
     * - Framework Response objects: Use built-in send() method
     * - PSR-7 responses: Send headers and body manually
     *
     * @param ResponseInterface $response The HTTP response to send
     * @return void
     * @see docs/Features/Operate/Boot/Application.md#method-sendresponse
     */
    private function sendResponse(ResponseInterface $response): void
    {
        // 1. Check for custom Framework Response with send() method
        if ($response instanceof Response) {
            $response->send();
            return;
        }

        // 2. Check for other objects that might have a send() method (Duck Typing)
        if (method_exists($response, 'send')) {
            /** @var mixed $duckResponse */
            $duckResponse = $response;
            $duckResponse->send();
            return;
        }

        if (! headers_sent()) {
            http_response_code($response->getStatusCode());
            foreach ($response->getHeaders() as $name => $values) {
                foreach ($values as $value) {
                    header(header: sprintf('%s: %s', $name, $value), replace: false);
                }
            }
        }

        echo $response->getBody();
    }

    /**
     * Terminate the current request scope.
     *
     * @see docs/Features/Operate/Boot/Application.md#method-terminate
     */
    public function terminate(): void
    {
        $this->container->endScope();
    }

    /**
     * Determine whether the application has already booted.
     *
     * @see docs/Features/Operate/Boot/Application.md#method-isbooted
     */
    public function isBooted(): bool
    {
        return $this->booted;
    }

    /**
     * Resolve a path relative to the application base path.
     *
     * @see docs/Features/Operate/Boot/Application.md#method-basepath
     */
    public function basePath(string $path = ''): string
    {
        return $this->basePath . ($path !== '' && $path !== '0' ? DIRECTORY_SEPARATOR . $path : '');
    }
}
