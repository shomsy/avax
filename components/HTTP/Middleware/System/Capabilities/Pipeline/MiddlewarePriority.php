<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Middleware\System\Capabilities\Pipeline;

final readonly class MiddlewarePriority
{
    public function __construct(
        private int $priority = 100,
    ) {}

    public function value() : int
    {
        return $this->priority;
    }
}
