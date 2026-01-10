<?php

declare(strict_types=1);

namespace Avax\Container\Features\Think\Prototype;

use Avax\Container\Features\Think\Model\MethodPrototype;
use Avax\Container\Features\Think\Model\PropertyPrototype;
use Avax\Container\Features\Think\Model\ServicePrototype;
use InvalidArgumentException;

/**
 * A fluent DSL for programmatically constructing service blueprints.
 *
 * The ServicePrototypeBuilder provides a flexible way to create 
 * {@see ServicePrototype} instances without relying on automatic reflection. 
 * This is essential for:
 * 1. Testing (creating mock blueprints with specific injection rules).
 * 2. Performance (generating hard-coded blueprints for extremely hot paths).
 * 3. Custom Integration (defining blueprints for classes that don't use attributes).
 *
 * @package Avax\Container\Features\Think\Prototype
 * @see docs/Features/Think/Prototype/ServicePrototypeBuilder.md
 */
final class ServicePrototypeBuilder
{
    /** @var string The fully qualified class name this prototype describes. */
    private string $class;

    /** @var bool Whether this service can be physically instantiated. */
    private bool $isInstantiable = true;

    /** @var MethodPrototype|null The constructor injection plan. */
    private ?MethodPrototype $constructor = null;

    /** @var PropertyPrototype[] List of property injection plans. */
    private array $properties = [];

    /** @var MethodPrototype[] List of method (setter) injection plans. */
    private array $methods = [];

    /**
     * Define the target class for this blueprint.
     *
     * @param string $class Fully qualified class name.
     * @return self
     * @see docs/Features/Think/Prototype/ServicePrototypeBuilder.md#method-for
     */
    public function for(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Set whether the container is allowed to instantiate this class.
     *
     * @param bool $state True for normal classes, false for abstracts/interfaces.
     * @return self
     * @see docs/Features/Think/Prototype/ServicePrototypeBuilder.md#method-setinstantiable
     */
    public function setInstantiable(bool $state): self
    {
        $this->isInstantiable = $state;

        return $this;
    }

    /**
     * Define the constructor requirements for this class.
     *
     * @param MethodPrototype|null $prototype The constructor parameter plan.
     * @return self
     * @see docs/Features/Think/Prototype/ServicePrototypeBuilder.md#method-withconstructor
     */
    public function withConstructor(?MethodPrototype $prototype): self
    {
        $this->constructor = $prototype;

        return $this;
    }

    /**
     * Add one or more property injection requirements.
     *
     * @param PropertyPrototype ...$prototypes
     * @return self
     * @see docs/Features/Think/Prototype/ServicePrototypeBuilder.md#method-addproperty
     */
    public function addProperty(PropertyPrototype ...$prototypes): self
    {
        foreach ($prototypes as $prototype) {
            $this->properties[] = $prototype;
        }

        return $this;
    }

    /**
     * Add one or more method (setter) injection requirements.
     *
     * @param MethodPrototype ...$prototypes
     * @return self
     * @see docs/Features/Think/Prototype/ServicePrototypeBuilder.md#method-addmethod
     */
    public function addMethod(MethodPrototype ...$prototypes): self
    {
        foreach ($prototypes as $prototype) {
            $this->methods[] = $prototype;
        }

        return $this;
    }

    /**
     * Semantic alias for build().
     *
     * @return ServicePrototype
     */
    public function makePrototype(): ServicePrototype
    {
        return $this->build();
    }

    /**
     * Finalize the builder and return an immutable blueprint.
     *
     * @return ServicePrototype The completed blueprint.
     * @throws InvalidArgumentException If the class name was never provided.
     *
     * @see docs/Features/Think/Prototype/ServicePrototypeBuilder.md#method-build
     */
    public function build(): ServicePrototype
    {
        if (! isset($this->class)) {
            throw new InvalidArgumentException(message: 'Cannot build ServicePrototype: target class has not been specified.');
        }

        return new ServicePrototype(
            class: $this->class,
            constructor: $this->constructor,
            injectedProperties: $this->properties,
            injectedMethods: $this->methods,
            isInstantiable: $this->isInstantiable
        );
    }
}
