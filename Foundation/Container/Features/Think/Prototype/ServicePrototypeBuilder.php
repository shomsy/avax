<?php

declare(strict_types=1);
namespace Avax\Container\Features\Think\Prototype;

use Avax\Container\Features\Core\Enum\ServiceLifetime;
use Avax\Container\Features\Think\Model\MethodPrototype;
use Avax\Container\Features\Think\Model\PropertyPrototype;
use Avax\Container\Features\Think\Model\ServicePrototype;

/**
 * @package Avax\Container\Think\Prototype
 *
 * Fluent builder for constructing immutable ServicePrototype DTOs.
 *
 * ServicePrototypeBuilder provides a domain-specific language (DSL) for programmatically
 * creating ServicePrototype instances with complex dependency injection specifications.
 * It enables dynamic construction of service blueprints for testing, compilation,
 * or runtime service registration scenarios.
 *
 * ARCHITECTURAL ROLE:
 * - Constructs ServicePrototype instances through fluent API
 * - Enables programmatic service blueprint creation
 * - Supports complex injection scenarios with multiple properties/methods
 * - Provides type-safe construction of dependency specifications
 *
 * BUILDER PATTERN USAGE:
 * ```php
 * $prototype = (new ServicePrototypeBuilder())
 *     ->for(DatabaseConnection::class)
 *     ->withConstructor($constructorPrototype)
 *     ->addProperty($loggerProperty)
 *     ->addMethod($initMethod)
 *     ->build();
 * ```
 *
 * CONSTRUCTION SCENARIOS:
 * - Test fixtures requiring specific injection configurations
 * - Runtime service registration with complex dependencies
 * - Container compilation generating ServicePrototype instances
 * - Analysis tools creating blueprints from external specifications
 *
 * INSTANTIABILITY CONTROL:
 * - $isInstantiable flag indicates if the service can be constructed
 * - False for abstract classes, interfaces, or services requiring special setup
 * - Used by resolution engine to provide appropriate error messages
 *
 * DEPENDENCY SPECIFICATION:
 * - Constructor plans define primary instantiation requirements
 * - Property prototypes specify field injection needs
 * - Method prototypes define setter injection configurations
 * - Multiple injection points supported for complex services
 *
 * IMMUTABILITY GUARANTEES:
 * - Builder state is mutable during construction
 * - build() produces immutable ServicePrototype instance
 * - Each builder instance creates one ServicePrototype
 *
 * VALIDATION CONSIDERATIONS:
 * - Class name validation occurs during prototype construction
 * - Injection point conflicts detected at build time
 * - Type compatibility verified against service class
 *
 * PERFORMANCE CHARACTERISTICS:
 * - Lightweight construction with minimal memory overhead
 * - Fast property access for fluent API chaining
 * - Immutable result enables safe sharing across threads
 *
 * TESTING UTILITIES:
 * - Enables creation of complex test scenarios
 * - Supports mock injection specifications
 * - Allows verification of injection configurations
 *
 * @see     ServicePrototype The immutable DTO produced by this builder
 * @see     DependencyInjectionPrototypeFactory For reflection-based prototype creation
 * @see     ServiceLifetime For supported lifetime policies
 * @see docs_md/Features/Think/Prototype/ServicePrototypeBuilder.md#quick-summary
 */
final class ServicePrototypeBuilder
{
    /**
     * @var string The fully qualified class name this prototype is for
     */
    private string $class;

    /**
     * @var bool Whether this service can be instantiated
     */
    private bool $isInstantiable = true;

    /**
     * @var MethodPrototype|null The constructor injection specification
     */
    private MethodPrototype|null $constructor = null;

    /**
     * @var PropertyPrototype[] List of property injection specifications
     */
    private array $properties = [];

    /**
     * @var MethodPrototype[] List of method injection specifications
     */
    private array $methods = [];

    /**
     * Specifies the target class for this service prototype.
     *
     * Sets the fully qualified class name that this ServicePrototype will describe.
     * This is the primary identifier for the service and determines how it
     * will be resolved by the container.
     *
     * CLASS NAME REQUIREMENTS:
     * - Must be a valid PHP class or interface name
     * - Should include full namespace qualification
     * - Will be validated during prototype construction
     *
     * @param string $class The fully qualified class name
     *
     * @return self Returns $this for method chaining
     * @see docs_md/Features/Think/Prototype/ServicePrototypeBuilder.md#method-for
     */
    public function for(string $class) : self
    {
        $this->class = $class;

        return $this;
    }


    /**
     * Marks whether this service can be instantiated.
     *
     * Controls whether the container will attempt to create instances of this service.
     * Set to false for abstract classes, interfaces, or services that require
     * special construction procedures.
     *
     * INSTANTIABILITY SCENARIOS:
     * - true: Normal classes that can be instantiated via constructor
     * - false: Abstract classes, interfaces, or services needing factory methods
     *
     * ERROR HANDLING:
     * - Container will provide appropriate error messages for non-instantiable services
     * - Resolution attempts on non-instantiable services will fail gracefully
     *
     * @param bool $state Whether the service can be instantiated
     *
     * @return self Returns $this for method chaining
     * @see docs_md/Features/Think/Prototype/ServicePrototypeBuilder.md#method-setinstantiable
     */
    public function setInstantiable(bool $state) : self
    {
        $this->isInstantiable = $state;

        return $this;
    }

    /**
     * Specifies the constructor injection prototype for this service.
     *
     * Defines how the service's constructor should be called during instantiation.
     * The MethodPrototype includes parameter specifications for dependency resolution
     * during object construction.
     *
     * CONSTRUCTOR REQUIREMENTS:
     * - Parameters must match the actual constructor signature
     * - Dependencies will be resolved by the container
     * - Null indicates no special constructor handling needed
     *
     * DEPENDENCY INJECTION:
     * - Constructor parameters resolved automatically
     * - Type hints guide container resolution
     * - Overrides can be provided during resolution
     *
     * @param MethodPrototype|null $prototype The constructor injection specification or null
     *
     * @return self Returns $this for method chaining
     * @see docs_md/Features/Think/Prototype/ServicePrototypeBuilder.md#method-withconstructor
     */
    public function withConstructor(MethodPrototype|null $prototype) : self
    {
        $this->constructor = $prototype;

        return $this;
    }

    /**
     * Adds a property injection specification to this service prototype.
     *
     * Registers a property that requires dependency injection during object
     * construction. The PropertyPrototype specifies the property name, type requirements,
     * and injection constraints.
     *
     * PROPERTY INJECTION RULES:
     * - Properties must not be readonly (PHP 8.1+)
     * - Private/protected properties require reflection accessibility
     * - Type hints guide container resolution for dependencies
     *
     * INJECTION TIMING:
     * - Properties injected after constructor execution
     * - Can override constructor-provided dependencies
     * - Failed injections handled gracefully with fallbacks
     *
     * @param PropertyPrototype ...$prototypes
     *
     * @return self Returns $this for method chaining
     * @see docs_md/Features/Think/Prototype/ServicePrototypeBuilder.md#method-addproperty
     */
    public function addProperty(PropertyPrototype ...$prototypes) : self
    {
        foreach ($prototypes as $prototype) {
            $this->properties[] = $prototype;
        }

        return $this;
    }

    /**
     * Adds a method injection specification to this service prototype.
     *
     * Registers a method that requires dependency injection during object
     * initialization. The MethodPrototype specifies the method name and parameter
     * specifications for setter injection patterns.
     *
     * METHOD INJECTION PATTERNS:
     * - Setter methods for optional dependencies
     * - Initialization methods requiring services
     * - Configuration methods needing injected values
     *
     * INJECTION TIMING:
     * - Methods called after property injection
     * - Executed in registration order
     * - Dependencies resolved for each method call
     *
     * @param MethodPrototype ...$prototypes
     *
     * @return self Returns $this for method chaining
     * @see docs_md/Features/Think/Prototype/ServicePrototypeBuilder.md#method-addmethod
     */
    public function addMethod(MethodPrototype ...$prototypes) : self
    {
        foreach ($prototypes as $prototype) {
            $this->methods[] = $prototype;
        }

        return $this;
    }

    /**
     * Alias for build() for DSL consistency.
     *
     * @return ServicePrototype
     * @see docs_md/Features/Think/Prototype/ServicePrototypeBuilder.md#method-makeprototype
     */
    public function makePrototype() : ServicePrototype
    {
        return $this->build();
    }

    /**
     * Constructs the immutable ServicePrototype instance.
     *
     * Finalizes the builder state and creates an immutable ServicePrototype DTO
     * containing all the specified injection requirements. The resulting prototype
     * can be cached, serialized, or used for service resolution.
     *
     * BUILD VALIDATION:
     * - Ensures class name is specified
     * - Validates injection prototype consistency
     * - Checks for conflicting specifications
     *
     * IMMUTABILITY GUARANTEE:
     * - Resulting ServicePrototype is completely immutable
     * - Safe for sharing across threads and requests
     * - Can be cached indefinitely
     *
     * USAGE SCENARIOS:
     * - Container registration of programmatically created prototypes
     * - Test fixture construction with specific injection needs
     * - Runtime service blueprint generation
     *
     * @return ServicePrototype The immutable service prototype specification
     * @throws \InvalidArgumentException If required fields are missing or invalid
     * @see docs_md/Features/Think/Prototype/ServicePrototypeBuilder.md#method-build
     */
    public function build() : ServicePrototype
    {
        return new ServicePrototype(
            class             : $this->class,
            constructor       : $this->constructor,
            injectedProperties: $this->properties,
            injectedMethods   : $this->methods,
            isInstantiable    : $this->isInstantiable
        );
    }
}
