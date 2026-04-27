<?php

declare(strict_types=1);

namespace Avax\Components\Router\System\Flows\RunRoute\Pipeline;

use Avax\Components\Container\DI\ContainerInterface;
use Avax\Components\HTTP\Dispatcher\ControllerDispatcher;
use Avax\Components\HTTP\Middleware\MiddlewareResolver;
use Avax\Components\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Factory class for constructing fully initialized route pipelines.
 */
final readonly class RoutePipelineFactory
{
    public function __construct(
        private ContainerInterface   $container,
        private ControllerDispatcher $dispatcher,
        private MiddlewareResolver   $middlewareResolver,
        private StageChain           $stageChain,
        private LoggerInterface      $logger = new NullLogger(),
    ) {}

    public function create(RouteDefinition $route) : RoutePipeline
    {
        $resolvedMiddleware = $this->middlewareResolver->resolve($route->middleware);

        $this->logger->debug('Assembling route pipeline.', [
            'route'      => $route->name ?: $route->path,
            'middleware' => $resolvedMiddleware,
        ]);

        return (new RoutePipeline(
            $route,
            $this->dispatcher,
            $this->container,
            $this->stageChain
        ))->through($resolvedMiddleware);
    }
}
