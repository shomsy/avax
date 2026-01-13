<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

use Avax\Container\Features\Core\Contracts\ContainerInterface;
use Avax\HTTP\Request\Request;
use Avax\HTTP\Router\Routing\Exceptions\StageOrderException;
use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Builds the middleware/stage execution chain and emits diagnostics.
 */
final class StageChain
{
    /** Stage ordering contract: stages precede middleware, then dispatch core. */
    private const ORDER_CONTRACT = ['stages', 'middleware', 'dispatch'];

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly LoggerInterface    $logger
    ) {}

    /**
     * Compose the pipeline stack.
     *
     * @param array<class-string> $stages
     * @param array<class-string> $middleware
     */
    public function create(array $stages, array $middleware, Closure $core) : Closure
    {
        $this->validateOrder(stages: $stages, middleware: $middleware);

        $pipeline = array_merge($stages, $middleware);

        if ($pipeline === []) {
            $this->logger->debug(message: 'Pipeline contains only core dispatcher.');

            return $core;
        }

        $this->logger->debug(message: 'Building route pipeline.', context: ['pipeline' => $pipeline]);

        return array_reduce(
            array_reverse($pipeline),
            fn(Closure $next, string $class) : Closure => fn(Request $request) : ResponseInterface => $this->invoke(class: $class, next: $next, request: $request),
            $core
        );
    }

    private function validateOrder(array $stages, array $middleware) : void
    {
        $pipeline = array_merge($stages, $middleware);
        $seen     = [];

        // Check for duplicates across entire pipeline
        foreach ($pipeline as $class) {
            if (isset($seen[$class])) {
                throw StageOrderException::duplicate(stage: $class, pipeline: $pipeline, expectedOrder: self::ORDER_CONTRACT);
            }
            $seen[$class] = true;
        }

        // Validate stage types (must implement RouteStage)
        foreach ($stages as $class) {
            if (! is_subclass_of(object_or_class: $class, class: RouteStage::class)) {
                throw StageOrderException::misordered(
                    stage        : $class,
                    pipeline     : $pipeline,
                    expectedOrder: self::ORDER_CONTRACT,
                    reason       : "Stage {$class} must implement RouteStage interface"
                );
            }
        }

        // Validate middleware types (must implement RouteMiddleware and NOT RouteStage)
        foreach ($middleware as $class) {
            if (! is_subclass_of(object_or_class: $class, class: RouteMiddleware::class)) {
                throw StageOrderException::misordered(
                    stage        : $class,
                    pipeline     : $pipeline,
                    expectedOrder: self::ORDER_CONTRACT,
                    reason       : "Middleware {$class} must implement RouteMiddleware interface"
                );
            }

            if (is_subclass_of(object_or_class: $class, class: RouteStage::class)) {
                throw StageOrderException::misordered(
                    stage        : $class,
                    pipeline     : $pipeline,
                    expectedOrder: self::ORDER_CONTRACT,
                    reason       : "Middleware {$class} must not implement RouteStage interface"
                );
            }
        }

        // Log successful validation
        $this->logger->debug(message: 'StageChain validation passed', context: [
            'stages_count'     => count($stages),
            'middleware_count' => count($middleware),
            'total_pipeline'   => count($pipeline),
            'order_contract'   => self::ORDER_CONTRACT
        ]);
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function invoke(string $class, Closure $next, Request $request) : ResponseInterface
    {
        $instance = $this->container->get(id: $class);

        if (! method_exists(object_or_class: $instance, method: 'handle')) {
            throw new RuntimeException(message: "Middleware or stage [{$class}] must have a handle() method.");
        }

        $this->logger->debug(message: 'Entering pipeline component.', context: ['component' => $class]);

        try {
            return $instance->handle($request, $next);
        } finally {
            $this->logger->debug(message: 'Exiting pipeline component.', context: ['component' => $class]);
        }
    }
}