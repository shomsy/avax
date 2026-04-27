<?php

declare(strict_types=1);

namespace Avax\Components\Middleware\System\Flows\RunMiddlewarePipeline;

final class RunMiddlewarePipeline
{
    public function run(array $middleware, mixed $request): mixed
    {
        $pipeline = array_reduce(
            array_reverse($middleware),
            fn(callable $next, MiddlewareInterface $middleware) => 
                fn() => $middleware->handle($request, $next),
            fn() => $request
        );

        return $pipeline();
    }
}