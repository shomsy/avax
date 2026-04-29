<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Capabilities\RouteCollection;

final class RouteName
{
    public function __construct(
        private string $name
    ) {}

    public function toString(): string { return $this->name; }
}
