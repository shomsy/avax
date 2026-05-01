<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Execution;

use Avax\Components\Application\Container\System\Capabilities\Declaration\Blueprints\CreateDependencyBlueprint;
use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Errors\ContainerException;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveDependencies;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveDependency;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveRequest;
use Throwable;

/**
 * Builds one object from its blueprint and resolved constructor dependencies.
 */
final readonly class BuildService
{
    private ResolveDependencies $dependencies;

    private CreateDependencyBlueprint $blueprints;

    public function __construct(
        CreateDependencyBlueprint $blueprints,
        ResolveDependencies $dependencies,
    )
    {
        $this->blueprints = $blueprints;
        $this->dependencies = $dependencies;
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @throws ContainerException
     */
    public function build(
        string          $class,
        ResolveDependency $resolver,
        ?array          $overrides = null,
        ?ResolveRequest $request = null,
    ) : object
    {
        $overrides ??= [];
        $serviceId = $request?->serviceId ?? $class;
        $path = $request?->getPath() ?? $serviceId;

        try {
            $blueprint = $this->blueprints->createFor(class: $class);
            if (! $blueprint->instantiable) {
                throw new ContainerException(
                    message: "Class [{$class}] is not instantiable for service [{$serviceId}]. "
                             . "Dependency path [{$path}]. "
                             . 'Likely fix: bind an instantiable concrete class or replace the abstract target.',
                );
            }

            $arguments = $blueprint->constructor !== null
                ? $this->dependencies->resolvePlan(
                    plan     : $blueprint->constructor,
                    overrides: $overrides,
                    resolver : $resolver,
                    request  : $request,
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
                previous: $exception,
            );
        }
    }
}
