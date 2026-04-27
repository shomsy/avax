<?php

declare(strict_types=1);

namespace Avax\Components\Router\System\Flows\RunRoute\Dispatch;

use Avax\Components\HTTP\Dispatcher\ControllerDispatcher;
use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\Components\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Psr\Http\Message\ResponseInterface;

/**
 * Executes a matched route by invoking the controller.
 */
final readonly class RouteExecutor
{
    public function __construct(private ControllerDispatcher $controllerDispatcher) {}

    public function execute(RouteDefinition $route, ServerRequest $request) : ResponseInterface
    {
        return $this->controllerDispatcher->dispatch($route->action, $request);
    }
}
