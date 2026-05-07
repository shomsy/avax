<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Capabilities\RouteCollection;

final readonly class RouteMethod
{
    public function __construct(
        private string $method,
    ) {}

    public function toString() : string
    {
        return strtoupper($this->method);
    }
}
