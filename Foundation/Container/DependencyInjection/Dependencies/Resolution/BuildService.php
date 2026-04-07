<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Dependencies\Resolution;

use Avax\Container\Errors\ContainerException;
use Avax\Container\DependencyInjection\Dependencies\Blueprints\CreateServiceBlueprint;
use Throwable;

final readonly class BuildService
{
    public function __construct(
        private CreateServiceBlueprint $blueprints,
        private ResolveDependencies    $dependencies
    ) {}

    public function build(
        string $class,
        ServiceResolver $resolver,
        array $overrides = [],
        ResolveRequest|null $request = null
    ) : object {
        try {
            $blueprint = $this->blueprints->createFor(class: $class);
            if (! $blueprint->instantiable) {
                throw new ContainerException(message: "Class [{$class}] is not instantiable.");
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
                message : "Failed to build service [{$class}]: {$exception->getMessage()}",
                previous: $exception
            );
        }
    }
}
