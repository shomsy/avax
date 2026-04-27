<?php

declare(strict_types=1);

namespace Avax\Components\Router\System\Flows\RunRoute\Pipeline;

use Avax\Components\Container\DI\ContainerInterface;
use Avax\Components\HTTP\Dispatcher\ControllerDispatcher;
use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\Components\HTTP\Response\Response;
use Avax\Components\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Manages the execution pipeline for handling incoming HTTP requests.
 */
final class RoutePipeline
{
    private array $middleware = [];
    private array $stages     = [];

    public function __construct(
        private readonly RouteDefinition      $route,
        private readonly ControllerDispatcher $dispatcher,
        private readonly ContainerInterface   $container,
        private readonly StageChain           $stageChain
    ) {}

    public function through(array $middleware) : self
    {
        $this->middleware = $middleware;

        return $this;
    }

    public function stages(array $stages) : self
    {
        $this->stages = $stages;

        return $this;
    }

    public function dispatch(ServerRequest $request) : ResponseInterface
    {
        try {
            if ($this->route->authorization !== null) {
                $request = $request->withAttribute('route:authorization', $this->route->authorization);
            }

            $route = $this->route;
            $core  = fn (ServerRequest $request) : ResponseInterface => $this->dispatcher->dispatch(
                $route->action,
                $request
            );

            $stack = $this->stageChain->create(
                $this->stages,
                $this->middleware,
                $core
            );

            return $stack($request);
        } catch (Throwable $e) {
            return Response::text('Internal Server Error', 500);
        }
    }
}
