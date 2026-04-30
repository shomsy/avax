<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Capabilities\RoutePattern;

final class CompileRoutePattern
{
    public function compile(string $pattern) : string
    {
        return '#^' . preg_replace('/\{([a-zA-Z0-9]+)\}/', '(?P<$1>[^/]+)', $pattern) . '$#';
    }
}
