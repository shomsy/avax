<?php

declare(strict_types=1);

namespace Avax\Components\Middleware\System\PublicSurface;

interface MiddlewareInterface
{
    public function handle(mixed $request, callable $next): mixed;
}