<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System;

use Avax\Components\Application\Container\System\Capabilities\Composition\Compilation\CompileReport;
use Avax\Components\Application\Container\System\Capabilities\Composition\CreateContainerConfig;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\DecoratorInterface;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\DependencyRegistration;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\RegisterForTarget;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Ownership\SliceContext;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Ownership\Views\CapabilitySliceView;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Ownership\Views\ConfigurationSliceView;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Ownership\Views\FlowSliceView;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Ownership\Views\FoundationSliceView;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Ownership\Views\RootCompositionView;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Providers\RegisterDependency;
use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Observability\RuntimeReport;
use Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Reports\InjectionReport;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveDependency;
use Avax\Components\Application\Container\System\Capabilities\Runtime\LazyProxy;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\ScopeInterface;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\ScopeKind;
use Avax\Components\Application\Container\System\Flows\ExplainService\ExplainService;
use Avax\Components\Application\Container\System\Flows\ExportGraph\ExportGraph;
use Avax\Components\Application\Container\System\Flows\ValidateComposition\ValidateComposition;
use Closure;
use InvalidArgumentException;
use ReflectionException;
use Throwable;

/**
 * Context-aware facade over the same underlying container runtime.
 */
readonly class ContextContainer implements ContainerInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(private Container $container, private ResolveDependency $resolveDependency, private array $context) {
    }

    public function has(string $id): bool
    {
        return $this->resolveDependency->hasInContext(id: $id, context: $this->context);
    }

    public function factory(string $abstract): Closure
    {
        return fn (array $parameters = []): object => $this->make(
            abstract  : $abstract,
            parameters: $parameters,
        );
    }

    /**
     * @throws Throwable
     */
    public function make(string $abstract, array $parameters = []): object
    {
        return $this->resolveDependency->makeInContext(id: $abstract, parameters: $parameters, context: $this->context);
    }

    /**
     * @throws ReflectionException
     */
    public function call(callable|string $callable, array $parameters = []): mixed
    {
        return $this->resolveDependency->callInContext(
            callable  : $callable,
            parameters: $parameters,
            context   : $this->context,
        );
    }

    /**
     * @throws ReflectionException
     */
    public function injectInto(object $target): object
    {
        return $this->resolveDependency->injectIntoInContext(target: $target, context: $this->context);
    }

    /**
     * @throws ReflectionException
     */
    public function canInject(object $target): bool
    {
        return $this->container->canInject(target: $target);
    }

    /**
     * @throws ReflectionException
     */
    public function inspectInjection(object $target): InjectionReport
    {
        return $this->container->inspectInjection(target: $target);
    }

    public function flush(): void
    {
        $this->assertGlobalMutationAllowed(action: 'flush runtime state');
        $this->container->flush();
    }

    protected function assertGlobalMutationAllowed(string $action): void
    {
        $slice = $this->slice();
        if (! $this->strictSliceBoundaries() || $slice === '') {
            return;
        }

        throw new InvalidArgumentException(
            message: sprintf('Strict slice view [%s] cannot %s. Use the root composition view for global runtime mutations.', $slice, $action),
        );
    }

    protected function slice(): string
    {
        return SliceContext::from(context: $this->context);
    }

    protected function strictSliceBoundaries(): bool
    {
        return $this->resolveDependency->sliceBoundaryMode() === CreateContainerConfig::SLICE_BOUNDARY_MODE_STRICT;
    }

    public function reset(): void
    {
        $this->assertGlobalMutationAllowed(action: 'reset runtime state');
        $this->container->reset();
    }

    /**
     * @param array<int, string|RegisterDependency> $providers
     */
    public function bootProviders(array $providers): void
    {
        $this->assertGlobalMutationAllowed(action: 'boot providers');
        $this->container->bootProviders(providers: $providers);
    }

    /**
     * @throws ReflectionException
     */
    public function validate(array $serviceIds = []): array
    {
        return $this->validateComposition()->validate(serviceIds: $serviceIds, context: $this->context);
    }

    private function validateComposition(): ValidateComposition
    {
        return new ValidateComposition(resolver: $this->resolveDependency);
    }

    /**
     * @throws ReflectionException
     */
    public function describeService(string $id): array
    {
        return $this->explainService()->describe(id: $id, context: $this->context);
    }

    private function explainService(): ExplainService
    {
        return new ExplainService(resolver: $this->resolveDependency);
    }

    /**
     * @throws ReflectionException
     */
    public function debugService(string $id): array
    {
        return $this->explainService()->describe(id: $id, context: $this->context);
    }

    /**
     * @throws ReflectionException
     */
    public function debugPlan(string $id): array
    {
        return $this->explainService()->debugPlan(id: $id, context: $this->context);
    }

    /**
     * @throws ReflectionException
     */
    public function debugGraph(string $id = ''): array
    {
        return $this->exportGraphFlow()->debugGraph(id: $id, context: $this->context);
    }

    private function exportGraphFlow(): ExportGraph
    {
        return new ExportGraph(resolver: $this->resolveDependency);
    }

    public function debugGovernance(string $id = ''): array
    {
        return $this->explainService()->debugGovernance(id: $id, context: $this->context);
    }

    public function debugArchitecture(string $id = ''): array
    {
        return $this->explainService()->debugArchitecture(id: $id, context: $this->context);
    }

    public function debugSlice(string $slice = ''): array
    {
        return $this->explainService()->debugSlice(slice: $slice, context: $this->context);
    }

    public function debugImports(string $slice = ''): array
    {
        return $this->explainService()->debugImports(slice: $slice, context: $this->context);
    }

    /**
     * @throws ReflectionException
     */
    public function debugExports(string $slice = ''): array
    {
        return $this->explainService()->debugExports(slice: $slice, context: $this->context);
    }

    public function debugVisibilityViolations(array $serviceIds = []): array
    {
        return $this->explainService()->debugVisibilityViolations(
            serviceIds: $serviceIds,
            context   : $this->context,
        );
    }

    /**
     * @throws ReflectionException
     */
    public function debugTags(string $tag): array
    {
        return $this->explainService()->debugTags(tag: $tag, context: $this->context);
    }

    /**
     * @throws ReflectionException
     */
    public function debugGroup(string $group): array
    {
        return $this->explainService()->debugGroup(group: $group, context: $this->context);
    }

    /**
     * @throws ReflectionException
     */
    public function debugSelection(string $id): array
    {
        return $this->explainService()->debugSelection(id: $id, context: $this->context);
    }

    public function debugAliases(): array
    {
        return $this->explainService()->debugAliases(context: $this->context);
    }

    public function debugScope(): array
    {
        return $this->explainService()->debugScope(context: $this->context);
    }

    public function env(string $key, mixed $default = null): mixed
    {
        return $this->container->env(key: $key, default: $default);
    }

    public function openScope(string $kind = ScopeKind::OPERATION, string $scopeId = ''): void
    {
        $this->container->openScope(kind: $kind, scopeId: $scopeId);
    }

    public function closeScope(?string $kind = null) : void
    {
        $this->container->closeScope(kind: $kind);
    }

    /**
     * @throws ReflectionException
     */
    public function compileContainer(array $serviceIds = []): void
    {
        $this->container->compileContainer(serviceIds: $this->compileTargets(serviceIds: $serviceIds));
    }

    /**
     * @param list<string> $serviceIds
     *
     * @return list<string>
     */
    protected function compileTargets(array $serviceIds): array
    {
        $slice = $this->slice();
        if (! $this->strictSliceBoundaries() || $slice === '') {
            return $serviceIds;
        }

        $visibleIds = array_values(array: array_map(
            callback: static fn (array $row): string => $row['serviceId'],
            array   : $this->resolveDependency->debugSliceInContext(slice: $slice, context: $this->context)['visible'] ?? [],
        ));

        if ($serviceIds === []) {
            return $visibleIds;
        }

        $resolvedIds = array_map(
            callback: fn (string $serviceId) : string => $this->resolveDependency->registrations()->resolveAlias(abstract: $serviceId),
            array   : $serviceIds,
        );
        $filtered = array_values(array: array_intersect($visibleIds, $resolvedIds));

        if (count(value: $filtered) !== count(value: array_unique(array: $resolvedIds))) {
            throw new InvalidArgumentException(
                message: sprintf('Strict slice view [%s] can only compile services visible from its boundary.', $slice),
            );
        }

        return $filtered;
    }

    /**
     * @throws ReflectionException
     */
    public function warmCompiled(array $serviceIds = []): void
    {
        $this->container->warmCompiled(serviceIds: $this->compileTargets(serviceIds: $serviceIds));
    }

    public function flushCompiled(): void
    {
        $this->assertGlobalMutationAllowed(action: 'flush compiled artifacts');
        $this->container->flushCompiled();
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function rebuildCompiled(array $serviceIds = []): void
    {
        $this->container->rebuildCompiled(serviceIds: $this->compileTargets(serviceIds: $serviceIds));
    }

    public function compileReport(array $serviceIds = []): ?CompileReport
    {
        return $this->container->compileReport(serviceIds: $this->compileTargets(serviceIds: $serviceIds));
    }

    public function runtimeReport(): RuntimeReport
    {
        return $this->container->runtimeReport();
    }

    public function hasAlias(string $alias): bool
    {
        return $this->container->hasAlias(alias: $alias);
    }

    public function isDeferred(string $id): bool
    {
        return $this->container->isDeferred(id: $id);
    }

    public function isLazy(string $id): bool
    {
        return $this->container->isLazy(id: $id);
    }

    public function isCompiled(string $id): bool
    {
        return $this->container->isCompiled(id: $id);
    }

    public function isWarmedUp(): bool
    {
        return $this->container->isWarmedUp();
    }

    public function scopes(): ScopeInterface
    {
        return $this->container->scopes();
    }

    /**
     * @throws Throwable
     */
    public function tagged(string $tag): array
    {
        return $this->resolveDependency->taggedInContext(tag: $tag, context: $this->context);
    }

    /**
     * @throws Throwable
     */
    public function grouped(string $group): array
    {
        return $this->resolveDependency->groupedInContext(group: $group, context: $this->context);
    }

    public function lazy(string $abstract): LazyProxy
    {
        return $this->resolveDependency->lazyInContext(abstract: $abstract, context: $this->context);
    }

    public function exportMetrics(): string
    {
        return $this->container->exportMetrics();
    }

    public function exportGraph(string $format = 'json', string $kind = 'dependency', string $id = ''): string
    {
        return $this->exportGraphFlow()->export(
            format : $format,
            kind   : $kind,
            id     : $id,
            context: $this->context,
        );
    }

    public function diffGraph(string $format = 'json', string $id = ''): string
    {
        return $this->exportGraphFlow()->diff(format: $format, id: $id, context: $this->context);
    }

    /**
     * @throws ReflectionException
     */
    public function why(string $id): array
    {
        return $this->exportGraphFlow()->why(id: $id, context: $this->context);
    }

    /**
     * @throws ReflectionException
     */
    public function whoUses(string $id): array
    {
        return $this->exportGraphFlow()->whoUses(id: $id, context: $this->context);
    }

    /**
     * @throws ReflectionException
     */
    public function whatBreaksIf(string $id): array
    {
        return $this->exportGraphFlow()->whatBreaksIf(id: $id, context: $this->context);
    }

    /**
     * @throws ReflectionException
     */
    public function showOwner(string $id): array
    {
        return $this->exportGraphFlow()->showOwner(id: $id, context: $this->context);
    }

    public function showSlice(string $slice = ''): array
    {
        return $this->exportGraphFlow()->showSlice(slice: $slice, context: $this->context);
    }

    public function alias(string $alias, string $abstract): void
    {
        $this->assertGlobalMutationAllowed(action: 'register aliases');
        $this->container->alias(alias: $alias, abstract: $abstract);
    }

    public function bind(string $abstract, mixed $concrete = null, bool $shared = false) : DependencyRegistration
    {
        return $this->applySliceMetadata(
            registration: $this->container->bind(abstract: $abstract, concrete: $concrete),
        );
    }

    protected function applySliceMetadata(DependencyRegistration $dependencyRegistration) : DependencyRegistration
    {
        $slice = SliceContext::from(context: $this->context);
        if ($slice === '') {
            return $dependencyRegistration;
        }

        if ($dependencyRegistration->metadata->ownerSlice === 'default') {
            $dependencyRegistration->ownedBy(ownerSlice: $slice);
        }

        $category = SliceContext::category(slice: $slice);
        if ($dependencyRegistration->metadata->category === 'configuration' && $category !== '') {
            $dependencyRegistration->category(category: $category);
        }

        if ($dependencyRegistration->metadata->visibility === 'public') {
            $dependencyRegistration->visibility(visibility: SliceContext::defaultVisibility(slice: $slice));
        }

        if ($dependencyRegistration->metadata->reason === 'registered service') {
            $dependencyRegistration->because(reason: sprintf('registered through slice view [%s]', $slice));
        }

        if ($dependencyRegistration->metadata->provenance === 'manual registration') {
            $dependencyRegistration->provenance(provenance: sprintf('slice view [%s]', $slice));
        }

        if ($this->strictSliceBoundaries()) {
            $category = SliceContext::category(slice: $slice);
            if ($category !== '') {
                $dependencyRegistration->lockOwnership(ownerSlice: $slice, category: $category);
            }
        }

        return $dependencyRegistration;
    }

    public function defer(string $abstract, mixed $concrete = null): DependencyRegistration
    {
        return $this->applySliceMetadata(
            registration: $this->container->defer(abstract: $abstract, concrete: $concrete),
        );
    }

    public function singleton(string $abstract, mixed $concrete = null): DependencyRegistration
    {
        return $this->applySliceMetadata(
            registration: $this->container->singleton(abstract: $abstract, concrete: $concrete),
        );
    }

    public function scoped(string $abstract, mixed $concrete = null): DependencyRegistration
    {
        return $this->applySliceMetadata(
            registration: $this->container->scoped(abstract: $abstract, concrete: $concrete),
        );
    }

    public function instance(string $abstract, object $instance): void
    {
        $this->container->instance(abstract: $abstract, instance: $instance);
        $registration = $this->resolveDependency->registrations()->get(abstract: $abstract);
        if ($registration instanceof DependencyRegistration) {
            $this->applySliceMetadata(registration: $registration);
        }
    }

    /**
     * @throws Throwable
     */
    public function get(string $id): mixed
    {
        return $this->resolveDependency->getInContext(id: $id, context: $this->context);
    }

    public function extend(string $abstract, callable $closure): void
    {
        $this->assertOwnedMutation(abstract: $abstract, action: 'extend');
        $this->container->extend(abstract: $abstract, closure: $closure);
    }

    protected function assertOwnedMutation(string $abstract, string $action): void
    {
        $slice = $this->slice();
        if (! $this->strictSliceBoundaries() || $slice === '') {
            return;
        }

        $registration = $this->resolveDependency->registrations()->get(
            abstract: $this->resolveDependency->registrations()->resolveAlias(abstract: $abstract),
        );
        if (! $registration instanceof DependencyRegistration) {
            throw new InvalidArgumentException(
                message: sprintf('Strict slice view [%s] cannot %s unknown service [%s].', $slice, $action, $abstract),
            );
        }

        if ($registration->metadata->ownerSlice !== $slice) {
            throw new InvalidArgumentException(
                message: sprintf('Strict slice view [%s] cannot %s service [%s] owned by [%s].', $slice, $action, $abstract, $registration->metadata->ownerSlice),
            );
        }
    }

    public function decorate(string $abstract, callable|DecoratorInterface|string $decorator): void
    {
        $this->assertOwnedMutation(abstract: $abstract, action: 'decorate');
        $this->container->decorate(abstract: $abstract, decorator: $decorator);
    }

    public function when(string $consumer): RegisterForTarget
    {
        $this->assertGlobalMutationAllowed(action: 'register contextual rules');

        return $this->container->when(consumer: $consumer);
    }

    public function tag(string|array $abstracts, string|array $tags): void
    {
        foreach ((array) $abstracts as $abstract) {
            if (is_string(value: $abstract) && $abstract !== '') {
                $this->assertOwnedMutation(abstract: $abstract, action: 'tag');
            }
        }

        $this->container->tag(abstracts: $abstracts, tags: $tags);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function forContext(array $context): ContainerInterface
    {
        if ($context === []) {
            return $this;
        }

        return new self(
            context : array_replace($this->context, $context),
            base    : $this->container,
            resolver: $this->resolveDependency,
        );
    }

    public function forSlice(string $slice): ContainerInterface
    {
        if (SliceContext::isRoot(slice: $slice)) {
            return new RootCompositionView(context: [], base: $this->container, resolver: $this->resolveDependency);
        }

        $context = SliceContext::with(context: $this->context, slice: $slice);

        return $this->sliceView(context: $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    protected function sliceView(array $context): ContainerInterface
    {
        return match (SliceContext::category(slice: SliceContext::from(context: $context))) {
            'flow'          => new FlowSliceView(context: $context, base: $this->container, resolver: $this->resolveDependency),
            'capability'    => new CapabilitySliceView(context: $context, base: $this->container, resolver: $this->resolveDependency),
            'configuration' => new ConfigurationSliceView(context: $context, base: $this->container, resolver: $this->resolveDependency),
            'foundation'    => new FoundationSliceView(context: $context, base: $this->container, resolver: $this->resolveDependency),
            default         => new self(context: $context, base: $this->container, resolver: $this->resolveDependency),
        };
    }
}
