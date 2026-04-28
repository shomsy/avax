<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\DI\Flows\ExplainService;

use Avax\Components\Application\Container\DI\Capabilities\Resolution\ServiceResolver;
use ReflectionException;

/**
 * Public diagnostics flow for service and slice explainability.
 */
final readonly class ExplainService
{
    private ServiceResolver $resolver;

    public function __construct(
        ServiceResolver $resolver
    )
    {
        $this->resolver = $resolver;
    }

    /**
     * @param string               $id
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     * @throws ReflectionException
     */
    public function describe(string $id, array $context = []) : array
    {
        if ($context === []) {
            return $this->resolver->describeService(id: $id);
        }

        return $this->resolver->describeServiceInContext(id: $id, context: $context);
    }

    /**
     * @param string               $id
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     * @throws ReflectionException
     */
    public function debugPlan(string $id, array $context = []) : array
    {
        if ($context === []) {
            return $this->resolver->debugPlan(id: $id);
        }

        return $this->resolver->debugPlanInContext(id: $id, context: $context);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function debugGovernance(string|null $id = null, array $context = []) : array
    {
        $id ??= '';
        if ($context === []) {
            return $this->resolver->debugGovernance(id: $id);
        }

        return $this->resolver->debugGovernanceInContext(id: $id, context: $context);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function debugArchitecture(string|null $id = null, array $context = []) : array
    {
        $id ??= '';
        if ($context === []) {
            return $this->resolver->debugArchitecture(id: $id);
        }

        return $this->resolver->debugArchitectureInContext(id: $id, context: $context);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function debugSlice(string|null $slice = null, array $context = []) : array
    {
        $slice ??= '';
        if ($context === []) {
            return $this->resolver->debugSlice(slice: $slice);
        }

        return $this->resolver->debugSliceInContext(slice: $slice, context: $context);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function debugImports(string|null $slice = null, array $context = []) : array
    {
        $slice ??= '';
        if ($context === []) {
            return $this->resolver->debugImports(slice: $slice);
        }

        return $this->resolver->debugImportsInContext(slice: $slice, context: $context);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     * @throws ReflectionException
     */
    public function debugExports(string|null $slice = null, array $context = []) : array
    {
        $slice ??= '';
        if ($context === []) {
            return $this->resolver->debugExports(slice: $slice);
        }

        return $this->resolver->debugExportsInContext(slice: $slice, context: $context);
    }

    /**
     * @param list<string>         $serviceIds
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function debugVisibilityViolations(array|null $serviceIds = null, array $context = []) : array
    {
        $serviceIds ??= [];
        if ($context === []) {
            return $this->resolver->debugVisibilityViolations(serviceIds: $serviceIds);
        }

        return $this->resolver->debugVisibilityViolationsInContext(
            serviceIds: $serviceIds,
            context   : $context
        );
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     * @throws ReflectionException
     */
    public function debugTags(string $tag, array $context = []) : array
    {
        if ($context === []) {
            return $this->resolver->debugTags(tag: $tag);
        }

        return $this->resolver->debugTagsInContext(tag: $tag, context: $context);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     * @throws ReflectionException
     */
    public function debugGroup(string $group, array $context = []) : array
    {
        if ($context === []) {
            return $this->resolver->debugGroup(group: $group);
        }

        return $this->resolver->debugGroupInContext(group: $group, context: $context);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     * @throws ReflectionException
     */
    public function debugSelection(string $id, array $context = []) : array
    {
        if ($context === []) {
            return $this->resolver->debugSelection(id: $id);
        }

        return $this->resolver->debugSelectionInContext(id: $id, context: $context);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, string>
     */
    public function debugAliases(array $context = []) : array
    {
        if ($context === []) {
            return $this->resolver->debugAliases();
        }

        return $this->resolver->debugAliasesInContext(context: $context);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function debugScope(array $context = []) : array
    {
        if ($context === []) {
            return $this->resolver->debugScope();
        }

        return $this->resolver->debugScopeInContext(context: $context);
    }
}
