<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Flows\MatchRoute;

final class MatchStaticRoute
{
    public function match(string $pattern, string $path): bool
    {
        return $pattern === $path;
    }
}
