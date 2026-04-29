<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Capabilities\RouteGroups;

final class RouteGroup
{
    public function __construct(
        private string $prefix = '',
        private array $middleware = []
    ) {}
}
