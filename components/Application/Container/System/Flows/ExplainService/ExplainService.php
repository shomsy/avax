<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Flows\ExplainService;

use Avax\Components\Application\Container\System\Capabilities\Resolution\ServiceResolver;
use ReflectionException;

/**
 * Public diagnostics flow for service and slice explainability.
 */
final readonly class ExplainService
{
    public function __construct(private ServiceResolver $serviceResolver)
    {
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     * @throws ReflectionException
     */
    public function describe(string $id, array $context = []) : array
    {
        if ($context === []) {
            return $this->serviceResolver->describeService(id: $id);
        }

        return $this->serviceResolver->describeServiceInContext(id: $id, context: $context);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     * @throws ReflectionException
     */
    public function debugPlan(string $id, array $context = []) : array
    {
        if ($context === []) {
            return $this->serviceResolver->debugPlan(id: $id);
        }

        return $this->serviceResolver->debugPlanInContext(id: $id, context: $context);
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
            return $this->serviceResolver->debugGovernance(id: $id);
        }

        return $this->serviceResolver->debugGovernanceInContext(id: $id, context: $context);
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
            return $this->serviceResolver->debugArchitecture(id: $id);
        }

        return $this->serviceResolver->debugArchitectureInContext(id: $id, context: $context);
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
            return $this->serviceResolver->debugSlice(slice: $slice);
        }

        return $this->serviceResolver->debugSliceInContext(slice: $slice, context: $context);
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
            return $this->serviceResolver->debugImports(slice: $slice);
        }

        return $this->serviceResolver->debugImportsInContext(slice: $slice, context: $context);
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
            return $this->serviceResolver->debugExports(slice: $slice);
        }

        return $this->serviceResolver->debugExportsInContext(slice: $slice, context: $context);
    }

    /**
     * @param list<string> $serviceIds
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function debugVisibilityViolations(array|null $serviceIds = null, array $context = []) : array
    {
        $serviceIds ??= [];
        if ($context === []) {
            return $this->serviceResolver->debugVisibilityViolations(serviceIds: $serviceIds);
        }

        return $this->serviceResolver->debugVisibilityViolationsInContext(
            serviceIds: $serviceIds,
            context   : $context,
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
            return $this->serviceResolver->debugTags(tag: $tag);
        }

        return $this->serviceResolver->debugTagsInContext(tag: $tag, context: $context);
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
            return $this->serviceResolver->debugGroup(group: $group);
        }

        return $this->serviceResolver->debugGroupInContext(group: $group, context: $context);
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
            return $this->serviceResolver->debugSelection(id: $id);
        }

        return $this->serviceResolver->debugSelectionInContext(id: $id, context: $context);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, string>
     */
    public function debugAliases(array $context = []) : array
    {
        if ($context === []) {
            return $this->serviceResolver->debugAliases();
        }

        return $this->serviceResolver->debugAliasesInContext(context: $context);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function debugScope(array $context = []) : array
    {
        if ($context === []) {
            return $this->serviceResolver->debugScope();
        }

        return $this->serviceResolver->debugScopeInContext(context: $context);
    }
}
