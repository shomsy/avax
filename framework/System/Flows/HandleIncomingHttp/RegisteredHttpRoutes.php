<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleIncomingHttp;

use Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Closure;

final readonly class RegisteredHttpRoutes
{
    /**
     * @param  array<string, list<RouteDefinition>>  $routesByMethod
     * @param  Closure|array<mixed>|string|null  $fallback
     */
    public function __construct(
        private array $routesByMethod,
        private Closure|array|string|null $fallback = null,
    ) {
    }

    /**
     * @return array<string, list<RouteDefinition>>
     */
    public function routesByMethod(): array
    {
        return $this->routesByMethod;
    }

    public function hasFallback(): bool
    {
        return $this->fallback !== null;
    }

    /**
     * @return Closure|array<mixed>|string|null
     */
    public function fallback(): Closure|array|string|null
    {
        return $this->fallback;
    }
}
