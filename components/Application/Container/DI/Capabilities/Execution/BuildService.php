<?php

declare(strict_types=1);

namespace Avax\Components\Container\DI\Capabilities\Execution;

use Avax\Components\Container\DI\Capabilities\Declaration\Blueprints\CreateServiceBlueprint;
use Avax\Components\Container\DI\Capabilities\Diagnostics\Errors\ContainerException;
use Avax\Components\Container\DI\Capabilities\Resolution\ResolveDependencies;
use Avax\Components\Container\DI\Capabilities\Resolution\ResolveRequest;
use Avax\Components\Container\DI\Capabilities\Resolution\ServiceResolver;
use Throwable;

/**
 * Builds one object from its blueprint and resolved constructor dependencies.
 */
final readonly class BuildService
{
    private ResolveDependencies    $dependencies;
    private CreateServiceBlueprint $blueprints;

    public function __construct(
        CreateServiceBlueprint $blueprints,
        ResolveDependencies    $dependencies
    )
    {
        $this->blueprints   = $blueprints;
        $this->dependencies = $dependencies;
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @throws ContainerException
     */
    public function build(
        string              $class,
        ServiceResolver     $resolver,
        array|null          $overrides = null,
        ResolveRequest|null $request = null
    ) : object
    {
        $overrides ??= [];
        $serviceId = $request?->serviceId ?? $class;
        $path      = $request?->getPath() ?? $serviceId;

        try {
            $blueprint = $this->blueprints->createFor(class: $class);
            if (! $blueprint->instantiable) {
                throw new ContainerException(
                    message: "Class [{$class}] is not instantiable for service [{$serviceId}]. "
                             . "Dependency path [{$path}]. "
                             . 'Likely fix: bind an instantiable concrete class or replace the abstract target.'
                );
            }

            $arguments = $blueprint->constructor !== null
                ? $this->dependencies->resolvePlan(
                    plan     : $blueprint->constructor,
                    overrides: $overrides,
                    resolver : $resolver,
                    request  : $request
                )
                : [];

            return new $class(...$arguments);
        } catch (Throwable $exception) {
            if ($exception instanceof ContainerException) {
                throw $exception;
            }

            throw new ContainerException(
                message : "Failed to build service [{$serviceId}] with class [{$class}]. "
                          . "Dependency path [{$path}]. "
                          . "Failure: {$exception->getMessage()}. "
                          . 'Likely fix: fix the constructor graph, provide missing runtime input, or replace the concrete class.',
                previous: $exception
            );
        }
    }
}
