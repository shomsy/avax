<?php

declare(strict_types=1);

namespace Avax\Components\Router\System\Flows\RunRoute\Pipeline;

/**
 * Interface for route middleware.
 */
interface RouteMiddleware
{
    public function handle($request, $next);
}
