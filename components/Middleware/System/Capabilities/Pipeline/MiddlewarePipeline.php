<?php

declare(strict_types=1);

namespace Avax\Components\Middleware\System\Capabilities\Pipeline;

final class MiddlewarePipeline
{
    private array $middleware = [];

    public function add(object $middleware): self
    {
        $this->middleware[] = $middleware;

        return $this;
    }

    public function execute(mixed $request): mixed
    {
        return $request;
    }
}