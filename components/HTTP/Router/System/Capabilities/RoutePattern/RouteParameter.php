<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Capabilities\RoutePattern;

final class RouteParameter
{
    public function __construct(
        private string $name,
        private mixed $value,
    ) {}

    public function name(): string
    {
        return $this->name;
    }

    public function value(): mixed
    {
        return $this->value;
    }
}
