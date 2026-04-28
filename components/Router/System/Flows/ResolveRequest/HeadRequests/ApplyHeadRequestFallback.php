<?php

declare(strict_types=1);

namespace Avax\Components\Router\System\Flows\ResolveRequest\HeadRequests;

use Avax\Components\Router\System\Flows\ResolveRequest\HttpRequestRouter;
use Avax\Components\Router\System\Flows\ResolveRequest\RouteResolutionContext;

/**
 * Handles HEAD request fallback by attempting to match a GET route instead.
 */
final readonly class ApplyHeadRequestFallback
{
    public function __construct(private HttpRequestRouter $router) {}

    public function apply(RouteResolutionContext $context) : void
    {
        if ($context->isResolved() || $context->method !== 'HEAD') {
            return;
        }

        $getResolution = $this->router->resolve('GET', $context->path, $context->domain);
        if ($getResolution->isResolved()) {
            $context->matched($getResolution->route(), $getResolution->parameters());
        }
    }
}
