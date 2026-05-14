<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Capabilities\RouteGroup;

use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteMethod;
use Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Files\Registrar;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface;

/**
 * Manages route registration within a prefixed group with optional middleware.
 *
 * Created by Router::group() to handle nested route registration
 * with automatic prefix and middleware application.
 */
final readonly class RouteGroupRegistrar implements RouterInterface
{
    public function __construct(
        private RouterInterface $router,
        private string $prefix,
        private mixed $groupMiddleware,
    ) {
    }

    private function prefixPath(string $path): string
    {
        $path = '/' . ltrim($path, '/');

        return $path === '/' ? $this->prefix : $this->prefix . $path;
    }

    private function applyRegistrar(Registrar $registrar): Registrar
    {
        if ($this->groupMiddleware !== null) {
            $registrar->middleware($this->groupMiddleware);
        }

        return $registrar;
    }

    #[\Override]
    public function get(string $path, mixed $action): Registrar
    {
        return $this->applyRegistrar($this->router->get($this->prefixPath($path), $action));
    }

    #[\Override]
    public function post(string $path, mixed $action): Registrar
    {
        return $this->applyRegistrar($this->router->post($this->prefixPath($path), $action));
    }

    #[\Override]
    public function put(string $path, mixed $action): Registrar
    {
        return $this->applyRegistrar($this->router->put($this->prefixPath($path), $action));
    }

    #[\Override]
    public function patch(string $path, mixed $action): Registrar
    {
        return $this->applyRegistrar($this->router->patch($this->prefixPath($path), $action));
    }

    #[\Override]
    public function delete(string $path, mixed $action): Registrar
    {
        return $this->applyRegistrar($this->router->delete($this->prefixPath($path), $action));
    }

    #[\Override]
    public function options(string $path, mixed $action): Registrar
    {
        return $this->applyRegistrar($this->router->options($this->prefixPath($path), $action));
    }

    #[\Override]
    public function head(string $path, mixed $action): Registrar
    {
        return $this->applyRegistrar($this->router->head($this->prefixPath($path), $action));
    }

    #[\Override]
    public function any(string $path, mixed $action): Registrar
    {
        return $this->applyRegistrar($this->router->any($this->prefixPath($path), $action));
    }

    #[\Override]
    public function group(string $prefix, callable $groupFn, string|array|callable|null $middleware = null): void
    {
        $this->router->group($this->prefix . rtrim($prefix, '/'), $groupFn, $middleware);
    }

    #[\Override]
    public function fallback(mixed $handler): void
    {
        $this->router->fallback($handler);
    }

    #[\Override]
    public function url(string $name, array $parameters = [], bool $absolute = false): string
    {
        return $this->router->url($name, $parameters, $absolute);
    }

    #[\Override]
    public function dispatch(\Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface $request): \Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface
    {
        return $this->router->dispatch($request);
    }
}
