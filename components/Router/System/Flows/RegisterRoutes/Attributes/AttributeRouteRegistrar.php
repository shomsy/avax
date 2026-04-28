<?php

declare(strict_types=1);

namespace Avax\Components\Router\System\Flows\RegisterRoutes\Attributes;

use Avax\Components\Router\System\Flows\RegisterRoutes\Attributes\Route as RouteAttribute;
use Avax\Components\Router\System\Flows\ResolveRequest\HttpRequestRouter;
use Avax\Components\Router\System\Foundation\Exceptions\DuplicateRouteException;
use Avax\Components\Router\System\Foundation\Exceptions\ReservedRouteNameException;
use Avax\Components\Router\System\PublicSurface\HttpMethod;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Scans controllers for Route attributes and registers them.
 */
final readonly class AttributeRouteRegistrar
{
    private HttpRequestRouter $router;

    public function __construct(HttpRequestRouter $router) { $this->router = $router; }

    /**
     * @param object|string $controller
     *
     * @throws DuplicateRouteException
     * @throws ReflectionException
     * @throws ReservedRouteNameException
     */
    public function register(object|string $controller) : void
    {
        $reflection  = new ReflectionClass(objectOrClass: $controller);
        $classRoutes = $this->instantiateRoutes(attributes: $reflection->getAttributes(name: RouteAttribute::class));

        foreach ($reflection->getMethods(filter: ReflectionMethod::IS_PUBLIC) as $method) {
            $methodRoutes = $this->instantiateRoutes(attributes: $method->getAttributes(name: RouteAttribute::class));

            if ($methodRoutes === []) {
                continue;
            }

            foreach ($methodRoutes as $route) {
                $bases = $classRoutes === [] ? [null] : $classRoutes;

                foreach ($bases as $baseRoute) {
                    $this->registerFromAttributes(
                        controllerClass: $reflection->getName(),
                        methodName     : $method->getName(),
                        route          : $route,
                        baseRoute      : $baseRoute
                    );
                }
            }
        }
    }

    /**
     * @param list<ReflectionAttribute> $attributes
     *
     * @return list<RouteAttribute>
     */
    private function instantiateRoutes(array $attributes) : array
    {
        return array_map(
            callback: static fn (ReflectionAttribute $attribute) => $attribute->newInstance(),
            array   : $attributes
        );
    }

    /**
     * @throws ReservedRouteNameException
     * @throws DuplicateRouteException
     */
    private function registerFromAttributes(
        string              $controllerClass,
        string              $methodName,
        RouteAttribute      $route,
        RouteAttribute|null $baseRoute
    ) : void
    {
        $path          = $this->normalizePath(prefix: $baseRoute?->path, path: $route->path);
        $methods       = $this->resolveMethods(route: $route, baseRoute: $baseRoute);
        $name          = $this->mergeNames(baseName: $baseRoute?->name, methodName: $route->name);
        $middleware    = array_merge($baseRoute?->middleware ?? [], $route->middleware);
        $constraints   = array_merge($baseRoute?->constraints ?? [], $route->constraints);
        $defaults      = array_merge($baseRoute?->defaults ?? [], $route->defaults);
        $attributes    = array_merge($baseRoute?->attributes ?? [], $route->attributes);
        $authorization = $route->authorize ?? $baseRoute?->authorize;
        $domain        = $route->domain ?? $baseRoute?->domain;

        foreach ($methods as $method) {
            $this->router->registerRoute(
                method: $method,
                path  : $path,
                action: [$controllerClass, $methodName]
            );
        }
    }

    private function normalizePath(string|null $prefix, string $path) : string
    {
        $prefix = $prefix ?? '';
        $prefix = $prefix === '' ? '' : '/' . ltrim(string: $prefix, characters: '/');

        $normalizedPath = rtrim(string: $prefix, characters: '/') . '/' . ltrim(string: $path, characters: '/');

        return preg_replace(pattern: '#//+#', replacement: '/', subject: $normalizedPath);
    }

    private function resolveMethods(RouteAttribute $route, RouteAttribute|null $baseRoute) : array
    {
        $methods = $route->methods !== [] ? $route->methods : ($baseRoute?->methods ?? [HttpMethod::GET->value]);

        return array_map(
            callback: static fn (string $method) => strtoupper(string: $method),
            array   : $methods
        );
    }

    private function mergeNames(string|null $baseName, string|null $methodName) : string|null
    {
        if ($baseName === null) {
            return $methodName;
        }

        if ($methodName === null) {
            return $baseName;
        }

        return rtrim(string: $baseName, characters: '.') . '.' . ltrim(string: $methodName, characters: '.');
    }
}
