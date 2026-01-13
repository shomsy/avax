<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

use Avax\HTTP\Router\HttpMethod;
use Avax\HTTP\Router\Routing\Attributes\Route as RouteAttribute;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

final readonly class AttributeRouteRegistrar
{
    public function __construct(private HttpRequestRouter $router) {}

    /**
     * @throws \ReflectionException
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
            callback: static fn(ReflectionAttribute $attribute) => $attribute->newInstance(),
            array   : $attributes
        );
    }

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
                method       : $method,
                path         : $path,
                action       : [$controllerClass, $methodName],
                middleware   : $middleware,
                name         : $name,
                constraints  : $constraints,
                defaults     : $defaults,
                domain       : $domain,
                attributes   : $attributes,
                authorization: $authorization
            );
        }
    }

    private function normalizePath(string|null $prefix, string $path) : string
    {
        $prefix = $prefix ?? '';
        $prefix = $prefix === '' ? '' : '/' . ltrim(string: $prefix, characters: '/');

        $normalizedPath = rtrim($prefix, '/') . '/' . ltrim(string: $path, characters: '/');

        // Normalize multiple consecutive slashes to single slash
        return preg_replace('#//+#', '/', $normalizedPath);
    }

    private function resolveMethods(RouteAttribute $route, RouteAttribute|null $baseRoute) : array
    {
        $methods = $route->methods !== [] ? $route->methods : ($baseRoute?->methods ?? [HttpMethod::GET->value]);

        return array_map(
            static fn(string $method) => strtoupper(string: $method),
            $methods
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
