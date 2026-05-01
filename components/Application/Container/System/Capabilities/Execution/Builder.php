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
    public function __construct(private CreateDependencyBlueprint $createDependencyBlueprint, private ResolveDependencies $resolveDependencies) {
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @throws ContainerException
     */
    public function build(
        string $class,
        ResolveDependency $resolveDependency,
        ?array            $overrides = null,
        ?ResolveRequest   $resolveRequest = null,
    ): object {
        $overrides ??= [];
        $serviceId = $resolveRequest?->serviceId ?? $class;
        $path = $resolveRequest?->getPath() ?? $serviceId;

        try {
            $blueprint = $this->createDependencyBlueprint->createFor(class: $class);
            if (! $blueprint->instantiable) {
                throw new ContainerException(
                    message: sprintf('Class [%s] is not instantiable for service [%s]. ', $class, $serviceId)
                             . sprintf('Dependency path [%s]. ', $path)
                             . 'Likely fix: bind an instantiable concrete class or replace the abstract target.',
                );
            }

            $arguments = $blueprint->constructor !== null
                ? $this->resolveDependencies->resolvePlan(
                    overrides: $overrides,
                    plan     : $blueprint->constructor,
                    resolver : $resolveDependency,
                    request  : $resolveRequest,
                )
                : [];

            return new $class(...$arguments);
        } catch (Throwable $throwable) {
            if ($throwable instanceof ContainerException) {
                throw $throwable;
            }

            throw new ContainerException(message: sprintf('Failed to build service [%s] with class [%s]. ', $serviceId, $class)
                                                  . sprintf('Dependency path [%s]. ', $path)
                                                  . sprintf('Failure: %s. ', $throwable->getMessage())
                                                  . 'Likely fix: fix the constructor graph, provide missing runtime input, or replace the concrete class.', code: $throwable->getCode(), previous: $throwable);
        }
    }
}
