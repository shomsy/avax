<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Dispatcher\System\PublicSurface;

use Avax\Components\HTTP\Dispatcher\System\Flows\DispatchRouteAction\DispatchRouteAction;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * ControllerDispatcher Public Surface.
 *
 * Exposes the route action dispatching mechanism.
 */
final readonly class ControllerDispatcher
{
    public function __construct(
        private DispatchRouteAction $dispatchRouteAction,
    ) {
    }

    public function dispatch(callable|array|string $action, ServerRequestInterface $serverRequest): ResponseInterface
    {
        return $this->dispatchRouteAction->execute($action, $serverRequest);
    }
}
