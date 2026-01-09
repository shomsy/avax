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
 * Service used for creating new instances of classes.
 *
 * @see docs_md/Features/Actions/Instantiate/Instantiator.md#quick-summary
 */
final class Instantiator
{
    /**
     * @param ServicePrototypeFactoryInterface $prototypes Prototype factory for constructor metadata
     * @param DependencyResolverInterface      $resolver   Constructor parameter resolver
     * @param ContainerInterface|null          $container  Container used for dependency resolution
     *
     * @see docs_md/Features/Actions/Instantiate/Instantiator.md#method-__construct
     */
    public function __construct(
        private readonly ServicePrototypeFactoryInterface $prototypes,
        private readonly DependencyResolverInterface $resolver,
        private ContainerInterface|null          $container = null
    ) {}

    /**
     * Set the container reference used for resolving constructor parameters.
     *
     * @param ContainerInterface $container
     * @return void
     * @see docs_md/Features/Actions/Instantiate/Instantiator.md#method-setcontainer
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * Build a class instance using analyzed constructor metadata.
     *
     * @param string           $class
     * @param array            $overrides
     * @param KernelContext|null $context
     *
     * @return object
     * @throws ContainerException
     * @see docs_md/Features/Actions/Instantiate/Instantiator.md#method-build
     */
    public function build(string $class, array $overrides = [], KernelContext|null $context = null): object
    {
        try {
            if (! class_exists($class)) {
                throw new ContainerException(message: "Cannot autowire: class [{$class}] not found.");
            }

            // Get prototype from metadata if available (passed from AnalyzePrototypeStep)
            $prototype = $context?->getMeta('analysis', 'prototype') ?? $this->prototypes->createFor(class: $class);

            $reflection = new ReflectionClass(objectOrClass: $class);
            if (! $reflection->isInstantiable()) {
                throw new ContainerException(message: "Cannot autowire: class [{$class}] is not instantiable.");
            }

            $resolvedParameters = [];
            if ($prototype->constructor) {
                if ($this->container === null) {
                    throw new ContainerException(message: 'Container not available for dependency resolution.');
                }

                $resolvedParameters = $this->resolver->resolveParameters(
                    parameters: $prototype->constructor->parameters,
                    overrides: $overrides,
                    container: $this->container,
                    context: $context
                );
            }

            return $prototype->constructor
                ? $reflection->newInstanceArgs(args: $resolvedParameters)
                : $reflection->newInstance();
        } catch (Throwable $e) {
            if ($e instanceof ContainerException) {
                throw $e;
            }
            throw new ContainerException(message: "Failed to build [{$class}]: " . $e->getMessage(), code: 0, previous: $e);
        }
    }
}
