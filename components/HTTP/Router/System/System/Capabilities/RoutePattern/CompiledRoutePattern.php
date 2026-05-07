<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\System\Capabilities\RoutePattern;

final readonly class CompiledRoutePattern
{
    public function __construct(
        private string $regex,
    ) {}

    public function regex() : string
    {
        return $this->regex;
    }
}
