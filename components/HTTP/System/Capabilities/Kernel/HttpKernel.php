<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Capabilities\Kernel;

use Avax\Components\HTTP\Middleware\System\PublicSurface\MiddlewareInterface;
use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface;
use Avax\Components\HTTP\System\PublicSurface\HttpInterface;

/**
 * HTTP Kernel - Central request handler.
 *
 * Accepts a request, runs it through the middleware pipeline,
 * dispatches to the router, returns a response, and calls terminate.
 */
final class HttpKernel implements HttpInterface
{
    /** @var list<MiddlewareInterface> */
    private array $middleware = [];

    private bool $booted = false;

    public function __construct(
        private readonly RouterInterface $router,
        private readonly BootHttpKernel $boot = new BootHttpKernel(),
        private readonly TerminateHttpKernel $terminator = new TerminateHttpKernel(),
    ) {
    }

    /**
     * Register middleware to be executed in order.
     */
    public function use(MiddlewareInterface $middleware): self
    {
        $this->middleware[] = $middleware;

        return $this;
    }

    /**
     * Boot the kernel (error handlers, service initialization).
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        $this->boot->boot();
        $this->booted = true;
    }

    /**
     * Handle an incoming request through the full lifecycle.
     *
     * 1. Boot kernel if not already booted
     * 2. Run middleware pipeline
     * 3. Dispatch to router
     * 4. Return response
     */
    public function handle(RequestInterface $request): ResponseInterface
    {
        $this->boot();

        $handler = fn (RequestInterface $req): ResponseInterface => $this->router->dispatch($req);

        // Build middleware pipeline in reverse order so first middleware runs first
        $pipeline = $this->middleware;
        while ($mw = array_pop($pipeline)) {
            $handler = $this->wrapMiddleware($mw, $handler);
        }

        return $handler($request);
    }

    /**
     * Terminate the request lifecycle (cleanup, logging, session write).
     */
    public function terminate(RequestInterface $request, ResponseInterface $response): void
    {
        $this->terminator->terminate($request, $response);
    }

    /**
     * Full request lifecycle: handle + terminate.
     */
    public function run(RequestInterface $request): ResponseInterface
    {
        $response = $this->handle($request);
        $this->terminate($request, $response);

        return $response;
    }

    /**
     * Wrap a handler with middleware execution.
     *
     * @param callable(RequestInterface) : ResponseInterface $next
     * @return callable(RequestInterface) : ResponseInterface
     */
    private function wrapMiddleware(MiddlewareInterface $middleware, callable $next): callable
    {
        return static fn (RequestInterface $request): ResponseInterface => $middleware->handle($request, $next);
    }

    /**
     * Get the registered router.
     */
    public function router(): RouterInterface
    {
        return $this->router;
    }

    /**
     * Get all registered middleware.
     *
     * @return list<MiddlewareInterface>
     */
    public function middleware(): array
    {
        return $this->middleware;
    }

    /**
     * Check if the kernel has been booted.
     */
    public function isBooted(): bool
    {
        return $this->booted;
    }
}
