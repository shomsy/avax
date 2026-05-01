<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Flows\ExplainService;

use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveDependency;
use ReflectionException;

/**
 * Public diagnostics flow for service and slice explainability.
 */
final readonly class ExplainService
{
    public function __construct(private ResolveDependency $resolveDependency) {}

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     * @throws ReflectionException
     */
    public function describe(string $id, array $context = []) : array
    {
        if ($context === []) {
            return $this->resolveDependency->describeService(id: $id);
        }

        return $this->resolveDependency->describeServiceInContext(id: $id, context: $context);
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
            return $this->resolveDependency->debugPlan(id: $id);
        }

        return $this->resolveDependency->debugPlanInContext(id: $id, context: $context);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function debugGovernance(string $id = null, array $context = []) : array
    {
        $id ??= '';
        if ($context === []) {
            return $this->resolveDependency->debugGovernance(id: $id);
        }

        return $this->resolveDependency->debugGovernanceInContext(id: $id, context: $context);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function debugArchitecture(string $id = null, array $context = []) : array
    {
        $id ??= '';
        if ($context === []) {
            return $this->resolveDependency->debugArchitecture(id: $id);
        }

        return $this->resolveDependency->debugArchitectureInContext(id: $id, context: $context);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function debugSlice(string $slice = null, array $context = []) : array
    {
        $slice ??= '';
        if ($context === []) {
            return $this->resolveDependency->debugSlice(slice: $slice);
        }

        return $this->resolveDependency->debugSliceInContext(slice: $slice, context: $context);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function debugImports(string $slice = null, array $context = []) : array
    {
        $slice ??= '';
        if ($context === []) {
            return $this->resolveDependency->debugImports(slice: $slice);
        }

        return $this->resolveDependency->debugImportsInContext(slice: $slice, context: $context);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     * @throws ReflectionException
     */
    public function debugExports(string $slice = null, array $context = []) : array
    {
        $slice ??= '';
        if ($context === []) {
            return $this->resolveDependency->debugExports(slice: $slice);
        }

        return $this->resolveDependency->debugExportsInContext(slice: $slice, context: $context);
    }

    /**
     * @param list<string> $serviceIds
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function debugVisibilityViolations(array $serviceIds = null, array $context = []) : array
    {
        $serviceIds ??= [];
        if ($context === []) {
            return $this->resolveDependency->debugVisibilityViolations(serviceIds: $serviceIds);
        }

        return $this->resolveDependency->debugVisibilityViolationsInContext(
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
            return $this->resolveDependency->debugTags(tag: $tag);
        }

        return $this->resolveDependency->debugTagsInContext(tag: $tag, context: $context);
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
            return $this->resolveDependency->debugGroup(group: $group);
        }

        return $this->resolveDependency->debugGroupInContext(group: $group, context: $context);
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
            return $this->resolveDependency->debugSelection(id: $id);
        }

        return $this->resolveDependency->debugSelectionInContext(id: $id, context: $context);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, string>
     */
    public function debugAliases(array $context = []) : array
    {
        if ($context === []) {
            return $this->resolveDependency->debugAliases();
        }

        return $this->resolveDependency->debugAliasesInContext(context: $context);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function debugScope(array $context = []) : array
    {
        if ($context === []) {
            return $this->resolveDependency->debugScope();
        }

        return $this->resolveDependency->debugScopeInContext(context: $context);
    }
}
