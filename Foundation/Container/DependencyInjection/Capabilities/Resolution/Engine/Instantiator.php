<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capabilities\Resolution\Engine;

use Avax\Container\DependencyInjection\Capabilities\Prototypes\Contracts\ServicePrototypeFactoryInterface;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Errors\ContainerException;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Kernel\KernelContext;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use Throwable;

/**
 * Builds concrete objects once the engine has selected a class.
 */
final readonly class Instantiator
{
    public function __construct(
        private ServicePrototypeFactoryInterface $prototypes,
        private DependencyResolver               $resolver
    ) {}

    public function build(string $class, ContainerInterface $container, array|null $overrides = null, KernelContext|null $context = null) : object
    {
        $overrides ??= [];

        try {
            if (! class_exists(class: $class)) {
                throw new ContainerException(message: "Cannot instantiate: class [{$class}] not found.");
            }

            /** @var \Avax\Container\DependencyInjection\Capabilities\Prototypes\Model\ServicePrototype $prototype */
            $prototype = $context?->getMeta(key: 'analysis', namespace: 'prototype')
                ?? $this->prototypes->createFor(class: $class);

            $reflection = new ReflectionClass(objectOrClass: $class);
            if (! $reflection->isInstantiable()) {
                throw new ContainerException(
                    message: "Cannot instantiate: class [{$class}] is abstract or has a private constructor."
                );
            }

            $resolvedParameters = [];
            if ($prototype->constructor !== null) {
                $resolvedParameters = $this->resolver->resolveParameters(
                    parameters: $prototype->constructor->parameters,
                    overrides : $overrides,
                    container : $container,
                    context   : $context
                );
            }

            return $prototype->constructor !== null
                ? $reflection->newInstanceArgs(args: $resolvedParameters)
                : $reflection->newInstance();
        } catch (Throwable $exception) {
            if ($exception instanceof ContainerException) {
                throw $exception;
            }

            throw new ContainerException(
                message : "Construction failed for [{$class}]: " . $exception->getMessage(),
                previous: $exception
            );
        }
    }
}
