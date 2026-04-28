<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Flows\RunRoute\Pipeline;

/**
 * Interface for custom route pipeline stages.
 */
interface RouteStage
{
    public function handle($request, $next);
}
