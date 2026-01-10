<?php

declare(strict_types=1);

namespace Avax\Container\Features\Actions\Inject;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Features\Actions\Inject\Contracts\PropertyInjectorInterface;
use Avax\Container\Features\Actions\Inject\Resolvers\PropertyResolution;
use Avax\Container\Features\Core\Contracts\ContainerInterface;
use Avax\Container\Features\Core\Contracts\ContainerInternalInterface;
use Avax\Container\Features\Core\Exceptions\ResolutionException;
use Avax\Container\Features\Core\Exceptions\ServiceNotFoundException;
use Avax\Container\Features\Think\Analyze\ReflectionTypeAnalyzer;
use Avax\Container\Features\Think\Model\PropertyPrototype;
use RuntimeException;

/**
 * Specialist for resolving and validating individual property injection points.
 *
 * The PropertyInjector focuses exclusively on the logic of finding the right 
 * value for a specific property. It follows a prioritized strategy similar to 
 * the {@see DependencyResolver}, but specifically tailored for properties 
 * (handling nullability, defaults, and type analysis).
 *
 * @package Avax\Container\Features\Actions\Inject
 * @see docs/Features/Actions/Inject/PropertyInjector.md
 */
final class PropertyInjector implements PropertyInjectorInterface
{
    /**
     * Initializes the property specialist.
     *
     * @param ContainerInterface|null $container     Container used to resolve property types.
     * @param ReflectionTypeAnalyzer  $typeAnalyzer  Helper for validating type resolvability.
     */
    public function __construct(
        private ContainerInterface|null $container,
        private ReflectionTypeAnalyzer  $typeAnalyzer = new ReflectionTypeAnalyzer()
    ) {}

    /**
     * Wire the container reference for recursive dependency resolution.
     *
     * @param ContainerInterface $container The application container instance.
     * @see docs/Features/Actions/Inject/PropertyInjector.md#method-setcontainer
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * Resolve the injection value for a specific property prototype.
     *
     * @param PropertyPrototype $property   The injection requirement profile.
     * @param array<string, mixed> $overrides  Manual values provided for this resolution.
     * @param KernelContext     $context    Tracking context for recursive resolution.
     * @param string            $ownerClass The class name that owns this property (for errors).
     *
     * @return PropertyResolution A wrapper containing the resolved value or status.
     * @throws ResolutionException If a required property cannot be satisfied.
     * @throws RuntimeException If the container reference is missing.
     *
     * @see docs/Features/Actions/Inject/PropertyInjector.md#method-resolve
     */
    public function resolve(
        PropertyPrototype $property,
        array             $overrides,
        KernelContext     $context,
        string            $ownerClass
    ): PropertyResolution {
        if ($this->container === null) {
            throw new RuntimeException(message: 'PropertyInjector container reference not initialized.');
        }

        $name = $property->name;

        // 1. Explicit Override (Highest Priority)
        if (array_key_exists(key: $name, array: $overrides)) {
            return PropertyResolution::resolved(value: $overrides[$name]);
        }

        // 2. Type-based Resolution
        if ($this->typeAnalyzer->canResolveType(type: $property->type)) {
            try {
                $type = (string) $property->type;

                // Circular Dependency Guard: Preserving context for children
                if ($this->container instanceof ContainerInternalInterface) {
                    return PropertyResolution::resolved(
                        value: $this->container->resolveContext(context: $context->child(serviceId: $type))
                    );
                }

                return PropertyResolution::resolved(
                    value: $this->container->get(id: $type)
                );
            } catch (ResolutionException | ServiceNotFoundException) {
                // Fall through to default/null handling
            }
        }

        // 3. Default Values (Skip injection if code-level default exists)
        if ($property->hasDefault) {
            return PropertyResolution::unresolved();
        }

        // 4. Nullable Fallback
        if ($property->allowsNull) {
            return PropertyResolution::resolved(value: null);
        }

        // 5. Hard Failure
        if ($property->required) {
            throw new ResolutionException(
                message: "Required property \${$name} in class {$ownerClass} cannot be resolved. " .
                    "No service found for type: " . ($property->type ?? 'null')
            );
        }

        return PropertyResolution::unresolved();
    }
}
