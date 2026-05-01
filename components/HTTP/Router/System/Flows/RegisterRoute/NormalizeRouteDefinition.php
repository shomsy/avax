<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Flows\RegisterRoute;

final class NormalizeRouteDefinition
{
    public function normalize(string $uri): string
    {
        return '/' . trim($uri, '/');
    }
}
