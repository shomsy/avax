<?php

declare(strict_types=1);

namespace Avax\Container\Features\Actions\Instantiate;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Features\Actions\Resolve\Contracts\DependencyResolverInterface;
use Avax\Container\Features\Core\Contracts\ContainerInterface;
use Avax\Container\Features\Core\Exceptions\ContainerException;
use Avax\Container\Features\Think\Prototype\Contracts\ServicePrototypeFactoryInterface;
use ReflectionClass;
use Throwable;

/**
 * The high-performance "Assembly Robot" for physical object instantiation.
 *
 * The Instantiator is responsible for the final act of object creation. It takes
 * a class name, looks up its analyzed constructor metadata (prototype), resolves
 * the necessary arguments via the {@see DependencyResolverInterface}, and
 * executes the constructor.
 *
 * @see     docs/Features/Actions/Instantiate/Instantiator.md
 */
final readonly class Instantiator
{
    /**
     * Initializes the instantiator with metadata and resolution helpers.
     *
     * @param ServicePrototypeFactoryInterface $prototypes Prototype factory for constructor metadata.
     * @param DependencyResolverInterface      $resolver   Constructor parameter resolver.
     */
    public function __construct(
        private ServicePrototypeFactoryInterface $prototypes,
        private DependencyResolverInterface      $resolver
    ) {}

    /**
     * Build a class instance using analyzed constructor metadata.
     *
     * @param string               $class     Fully qualified class name to instantiate.
     * @param ContainerInterface   $container The container used for resolving constructor dependencies.
     * @param array<string, mixed> $overrides Manual constructor arguments (Name => Value).
     * @param KernelContext|null   $context   Current resolution context for loop detection and metadata.
     *
     * @return object The newly created instance.
     *
     * @throws ContainerException If the class is not instantiable or resolution fails.
     *
     * @see docs/Features/Actions/Instantiate/Instantiator.md#method-build
     */
    public function build(string $class, ContainerInterface $container, array|null $overrides = null, KernelContext|null $context = null) : object
    {
        $overrides ??= [];
        try {
            if (! class_exists(class: $class)) {
                throw new ContainerException(message: "Cannot instantiate: class [{$class}] not found.");
            }

            // 1. Fetch analyzed metadata (from context or factory)
            /** @var \Avax\Container\Features\Think\Model\ServicePrototype $prototype */
            $prototype = $context?->getMeta(key: 'analysis', namespace: 'prototype')
                ?? $this->prototypes->createFor(class: $class);

            $reflection = new ReflectionClass(objectOrClass: $class);
            if (! $reflection->isInstantiable()) {
                throw new ContainerException(message: "Cannot instantiate: class [{$class}] is abstract or has a private constructor.");
            }

            // 2. Resolve constructor arguments
            $resolvedParameters = [];
            if ($prototype->constructor) {
                $resolvedParameters = $this->resolver->resolveParameters(
                    parameters: $prototype->constructor->parameters,
                    overrides : $overrides,
                    container : $container,
                    context   : $context
                );
            }

            // 3. Execute constructor
            return $prototype->constructor
                ? $reflection->newInstanceArgs(args: $resolvedParameters)
                : $reflection->newInstance();
        } catch (Throwable $e) {
            if ($e instanceof ContainerException) {
                throw $e;
            }
            throw new ContainerException(
                message : "Construction failed for [{$class}]: " . $e->getMessage(),
                code    : 0,
                previous: $e
            );
        }
    }
}
