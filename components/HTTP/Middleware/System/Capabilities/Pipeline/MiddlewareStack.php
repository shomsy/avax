<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Middleware\System\Capabilities\Pipeline;

final class MiddlewareStack
{
    private array $stack = [];

    public function push($middleware) : void
    {
        $this->stack[] = $middleware;
    }

    public function all() : array
    {
        return $this->stack;
    }
}
