<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Capabilities\RoutePattern;

final class CompiledRoutePattern
{
    public function __construct(
        private string $regex
    ) {}

    public function regex(): string
    {
        return $this->regex;
    }
}
