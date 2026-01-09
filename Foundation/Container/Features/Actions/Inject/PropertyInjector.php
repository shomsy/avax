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
 * Service for resolving individual injectable properties.
 *
 * @see docs_md/Features/Actions/Inject/PropertyInjector.md#quick-summary
 */
final class PropertyInjector implements PropertyInjectorInterface
{
    /**
     * @param ContainerInterface|null $container     Container used to resolve property types
     * @param ReflectionTypeAnalyzer  $typeAnalyzer  Helper for checking type resolvability
     *
     * @see docs_md/Features/Actions/Inject/PropertyInjector.md#method-__construct
     */
    public function __construct(
        private ContainerInterface|null $container,
        private ReflectionTypeAnalyzer  $typeAnalyzer = new ReflectionTypeAnalyzer()
    ) {}

    /**
     * Set the container reference for property resolution.
     *
     * @param ContainerInterface $container
     * @return void
     * @see docs_md/Features/Actions/Inject/PropertyInjector.md#method-setcontainer
     */
    public function setContainer(ContainerInterface $container) : void
    {
        $this->container = $container;
    }

    /**
     * Resolve the injection value for a property prototype.
     *
     * @param PropertyPrototype $property
     * @param array             $overrides
     * @param KernelContext     $context
     * @param string            $ownerClass
     *
     * @return PropertyResolution
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException
     * @throws RuntimeException When the container reference is not initialized
     * @see docs_md/Features/Actions/Inject/PropertyInjector.md#method-resolve
     */
    public function resolve(
        PropertyPrototype $property,
        array             $overrides,
        KernelContext     $context,
        string            $ownerClass
    ) : PropertyResolution
    {
        if ($this->container === null) {
            throw new RuntimeException('PropertyInjector container reference not initialized.');
        }

        if (array_key_exists($property->name, $overrides)) {
            return PropertyResolution::resolved(value: $overrides[$property->name]);
        }

        if ($this->typeAnalyzer->canResolveType(type: $property->type)) {
            try {
                // Circular Dependency Guard: Preserving context for children
                if ($this->container instanceof ContainerInternalInterface) {
                    return PropertyResolution::resolved(
                        value: $this->container->resolveContext(context: $context->child(serviceId: (string) $property->type))
                    );
                }

                return PropertyResolution::resolved(
                    value: $this->container->get(id: (string) $property->type)
                );
            } catch (ResolutionException|ServiceNotFoundException) {
                // Fall through to default/null handling.
            }
        }

        if ($property->hasDefault) {
            return PropertyResolution::unresolved();
        }

        if ($property->allowsNull) {
            return PropertyResolution::resolved(value: null);
        }

        // Required property that cannot be resolved - throw exception
        if ($property->required) {
            throw new ResolutionException(
                message: "Required property \${$property->name} in class {$ownerClass} cannot be resolved. " .
                "No service found for type: " . ($property->type ?? 'null')
            );
        }

        return PropertyResolution::unresolved();
    }
}
