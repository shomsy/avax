<?php

declare(strict_types=1);

namespace Avax\Components\Middleware\System\PublicSurface;

final class Middleware implements MiddlewareInterface
{
    public function handle(mixed $request, callable $next): mixed
    {
        return $next($request);
    }
}