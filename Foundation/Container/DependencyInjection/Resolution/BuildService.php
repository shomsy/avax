<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Resolution;

use Avax\Container\DependencyInjection\Errors\ContainerException;
use ReflectionClass;
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
            $reflection = new ReflectionClass($class);
            if (! $reflection->isInstantiable()) {
                throw new ContainerException(message: "Class [{$class}] is not instantiable.");
            }

            $blueprint = $this->blueprints->createFor(class: $class);
            $arguments = $blueprint->constructor !== null
                ? $this->dependencies->resolveParameters(
                    parameters: $blueprint->constructor->getParameters(),
                    overrides : $overrides,
                    resolver  : $resolver,
                    request   : $request
                )
                : [];

            return $reflection->newInstanceArgs($arguments);
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
