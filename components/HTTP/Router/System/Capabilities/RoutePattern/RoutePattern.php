<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Capabilities\RoutePattern;

final class RoutePattern
{
    public function __construct(
        private string $pattern,
    ) {}

    public function toString() : string
    {
        return $this->pattern;
    }
}
