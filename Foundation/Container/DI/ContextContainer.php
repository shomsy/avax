<?php

declare(strict_types=1);

namespace Avax\Container\DI;

use Avax\Container\DI\Capabilities\Composition\Compilation\CompileReport;
use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;
use Avax\Container\DI\Capabilities\Declaration\Bindings\DecoratorInterface;
use Avax\Container\DI\Capabilities\Declaration\Bindings\RegisterForTarget;
use Avax\Container\DI\Capabilities\Declaration\Bindings\ServiceRegistration;
use Avax\Container\DI\Capabilities\Declaration\Ownership\SliceContext;
use Avax\Container\DI\Capabilities\Declaration\Ownership\Views\CapabilitySliceView;
use Avax\Container\DI\Capabilities\Declaration\Ownership\Views\ConfigurationSliceView;
use Avax\Container\DI\Capabilities\Declaration\Ownership\Views\FlowSliceView;
use Avax\Container\DI\Capabilities\Declaration\Ownership\Views\FoundationSliceView;
use Avax\Container\DI\Capabilities\Declaration\Ownership\Views\RootCompositionView;
use Avax\Container\DI\Capabilities\Declaration\Providers\ServiceProviderInterface;
use Avax\Container\DI\Capabilities\Diagnostics\Observability\RuntimeReport;
use Avax\Container\DI\Capabilities\Execution\Injection\Reports\InjectionReport;
use Avax\Container\DI\Capabilities\Resolution\ServiceResolver;
use Avax\Container\DI\Capabilities\Runtime\LazyProxy;
use Avax\Container\DI\Capabilities\Runtime\Scopes\ScopeInterface;
use Avax\Container\DI\Capabilities\Runtime\Scopes\ScopeKind;
use Avax\Container\DI\Flows\ExplainService\ExplainService;
use Avax\Container\DI\Flows\ExportGraph\ExportGraph;
use Avax\Container\DI\Flows\ValidateComposition\ValidateComposition;
use Closure;
use InvalidArgumentException;

/**
 * Context-aware facade over the same underlying container runtime.
 */
readonly class ContextContainer implements ContainerInterface
{
    private array           $context;
    private ServiceResolver $resolver;
    private Container       $base;

    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        Container       $base,
        ServiceResolver $resolver,
        array           $context
    )
    {
        $this->base     = $base;
        $this->resolver = $resolver;
        $this->context  = $context;
    }

    public function has(string $id) : bool
    {
        return $this->resolver->hasInContext(id: $id, context: $this->context);
    }

    public function factory(string $abstract) : Closure
    {
        return fn (array $parameters = []) : object => $this->make(
            abstract  : $abstract,
            parameters: $parameters
        );
    }

    /**
     * @throws \Throwable
     */
    public function make(string $abstract, array $parameters = []) : object
    {
        return $this->resolver->makeInContext(id: $abstract, parameters: $parameters, context: $this->context);
    }

    /**
     * @throws \ReflectionException
     */
    public function call(callable|string $callable, array $parameters = []) : mixed
    {
        return $this->resolver->callInContext(
            callable  : $callable,
            parameters: $parameters,
            context   : $this->context
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function injectInto(object $target) : object
    {
        return $this->resolver->injectIntoInContext(target: $target, context: $this->context);
    }

    public function canInject(object $target) : bool
    {
        return $this->base->canInject(target: $target);
    }

    public function inspectInjection(object $target) : InjectionReport
    {
        return $this->base->inspectInjection(target: $target);
    }

    public function flush() : void
    {
        $this->assertGlobalMutationAllowed(action: 'flush runtime state');
        $this->base->flush();
    }

    protected function assertGlobalMutationAllowed(string $action) : void
    {
        $slice = $this->slice();
        if (! $this->strictSliceBoundaries() || $slice === '') {
            return;
        }

        throw new InvalidArgumentException(
            message: "Strict slice view [{$slice}] cannot {$action}. Use the root composition view for global runtime mutations."
        );
    }

    protected function slice() : string
    {
        return SliceContext::from(context: $this->context);
    }

    protected function strictSliceBoundaries() : bool
    {
        return $this->resolver->sliceBoundaryMode() === CreateContainerConfig::SLICE_BOUNDARY_MODE_STRICT;
    }

    public function reset() : void
    {
        $this->assertGlobalMutationAllowed(action: 'reset runtime state');
        $this->base->reset();
    }

    /**
     * @param array<int, string|ServiceProviderInterface> $providers
     */
    public function bootProviders(array $providers) : void
    {
        $this->assertGlobalMutationAllowed(action: 'boot providers');
        $this->base->bootProviders(providers: $providers);
    }

    public function validate(array $serviceIds = []) : array
    {
        return $this->validateComposition()->validate(serviceIds: $serviceIds, context: $this->context);
    }

    private function validateComposition() : ValidateComposition
    {
        return new ValidateComposition(resolver: $this->resolver);
    }

    /**
     * @throws \ReflectionException
     */
    public function describeService(string $id) : array
    {
        return $this->explainService()->describe(id: $id, context: $this->context);
    }

    private function explainService() : ExplainService
    {
        return new ExplainService(resolver: $this->resolver);
    }

    /**
     * @throws \ReflectionException
     */
    public function debugService(string $id) : array
    {
        return $this->explainService()->describe(id: $id, context: $this->context);
    }

    /**
     * @throws \ReflectionException
     */
    public function debugPlan(string $id) : array
    {
        return $this->explainService()->debugPlan(id: $id, context: $this->context);
    }

    public function debugGraph(string $id = '') : array
    {
        return $this->exportGraphFlow()->debugGraph(id: $id, context: $this->context);
    }

    private function exportGraphFlow() : ExportGraph
    {
        return new ExportGraph(resolver: $this->resolver);
    }

    public function debugGovernance(string $id = '') : array
    {
        return $this->explainService()->debugGovernance(id: $id, context: $this->context);
    }

    public function debugArchitecture(string $id = '') : array
    {
        return $this->explainService()->debugArchitecture(id: $id, context: $this->context);
    }

    public function debugSlice(string $slice = '') : array
    {
        return $this->explainService()->debugSlice(slice: $slice, context: $this->context);
    }

    public function debugImports(string $slice = '') : array
    {
        return $this->explainService()->debugImports(slice: $slice, context: $this->context);
    }

    public function debugExports(string $slice = '') : array
    {
        return $this->explainService()->debugExports(slice: $slice, context: $this->context);
    }

    public function debugVisibilityViolations(array $serviceIds = []) : array
    {
        return $this->explainService()->debugVisibilityViolations(
            serviceIds: $serviceIds,
            context   : $this->context
        );
    }

    public function debugTags(string $tag) : array
    {
        return $this->explainService()->debugTags(tag: $tag, context: $this->context);
    }

    public function debugGroup(string $group) : array
    {
        return $this->explainService()->debugGroup(group: $group, context: $this->context);
    }

    public function debugSelection(string $id) : array
    {
        return $this->explainService()->debugSelection(id: $id, context: $this->context);
    }

    public function debugAliases() : array
    {
        return $this->explainService()->debugAliases(context: $this->context);
    }

    public function debugScope() : array
    {
        return $this->explainService()->debugScope(context: $this->context);
    }

    public function env(string $key, mixed $default = null) : mixed
    {
        return $this->base->env(key: $key, default: $default);
    }

    public function openScope(string $kind = ScopeKind::OPERATION, string $scopeId = '') : void
    {
        $this->base->openScope(kind: $kind, scopeId: $scopeId);
    }

    public function closeScope(string|null $kind = null) : void
    {
        $this->base->closeScope(kind: $kind);
    }

    public function compileContainer(array $serviceIds = []) : void
    {
        $this->base->compileContainer(serviceIds: $this->compileTargets(serviceIds: $serviceIds));
    }

    /**
     * @param list<string> $serviceIds
     *
     * @return list<string>
     */
    protected function compileTargets(array $serviceIds) : array
    {
        $slice = $this->slice();
        if (! $this->strictSliceBoundaries() || $slice === '') {
            return $serviceIds;
        }

        $visibleIds = array_values(array_map(
                                       static fn (array $row) : string => $row['serviceId'],
                                       $this->resolver->debugSliceInContext(slice: $slice, context: $this->context)['visible'] ?? []
                                   ));

        if ($serviceIds === []) {
            return $visibleIds;
        }

        $resolvedIds = array_map(
            fn (string $serviceId) : string => $this->resolver->registrations()->resolveAlias(abstract: $serviceId),
            $serviceIds
        );
        $filtered    = array_values(array_intersect($visibleIds, $resolvedIds));

        if (count($filtered) !== count(array_unique($resolvedIds))) {
            throw new InvalidArgumentException(
                message: "Strict slice view [{$slice}] can only compile services visible from its boundary."
            );
        }

        return $filtered;
    }

    public function warmCompiled(array $serviceIds = []) : void
    {
        $this->base->warmCompiled(serviceIds: $this->compileTargets(serviceIds: $serviceIds));
    }

    public function flushCompiled() : void
    {
        $this->assertGlobalMutationAllowed(action: 'flush compiled artifacts');
        $this->base->flushCompiled();
    }

    public function rebuildCompiled(array $serviceIds = []) : void
    {
        $this->base->rebuildCompiled(serviceIds: $this->compileTargets(serviceIds: $serviceIds));
    }

    public function compileReport(array $serviceIds = []) : CompileReport|null
    {
        return $this->base->compileReport(serviceIds: $this->compileTargets(serviceIds: $serviceIds));
    }

    public function runtimeReport() : RuntimeReport
    {
        return $this->base->runtimeReport();
    }

    public function hasAlias(string $alias) : bool
    {
        return $this->base->hasAlias(alias: $alias);
    }

    public function isDeferred(string $id) : bool
    {
        return $this->base->isDeferred(id: $id);
    }

    public function isLazy(string $id) : bool
    {
        return $this->base->isLazy(id: $id);
    }

    public function isCompiled(string $id) : bool
    {
        return $this->base->isCompiled(id: $id);
    }

    public function isWarmedUp() : bool
    {
        return $this->base->isWarmedUp();
    }

    public function scopes() : ScopeInterface
    {
        return $this->base->scopes();
    }

    public function tagged(string $tag) : array
    {
        return $this->resolver->taggedInContext(tag: $tag, context: $this->context);
    }

    public function grouped(string $group) : array
    {
        return $this->resolver->groupedInContext(group: $group, context: $this->context);
    }

    public function lazy(string $abstract) : LazyProxy
    {
        return $this->resolver->lazyInContext(abstract: $abstract, context: $this->context);
    }

    public function exportMetrics() : string
    {
        return $this->base->exportMetrics();
    }

    public function exportGraph(string $format = 'json', string $kind = 'dependency', string $id = '') : string
    {
        return $this->exportGraphFlow()->export(
            format : $format,
            kind   : $kind,
            id     : $id,
            context: $this->context
        );
    }

    public function diffGraph(string $format = 'json', string $id = '') : string
    {
        return $this->exportGraphFlow()->diff(format: $format, id: $id, context: $this->context);
    }

    public function why(string $id) : array
    {
        return $this->exportGraphFlow()->why(id: $id, context: $this->context);
    }

    public function whoUses(string $id) : array
    {
        return $this->exportGraphFlow()->whoUses(id: $id, context: $this->context);
    }

    public function whatBreaksIf(string $id) : array
    {
        return $this->exportGraphFlow()->whatBreaksIf(id: $id, context: $this->context);
    }

    public function showOwner(string $id) : array
    {
        return $this->exportGraphFlow()->showOwner(id: $id, context: $this->context);
    }

    public function showSlice(string $slice = '') : array
    {
        return $this->exportGraphFlow()->showSlice(slice: $slice, context: $this->context);
    }

    public function alias(string $alias, string $abstract) : void
    {
        $this->assertGlobalMutationAllowed(action: 'register aliases');
        $this->base->alias(alias: $alias, abstract: $abstract);
    }

    public function bind(string $abstract, mixed $concrete = null) : ServiceRegistration
    {
        return $this->applySliceMetadata(
            registration: $this->base->bind(abstract: $abstract, concrete: $concrete)
        );
    }

    protected function applySliceMetadata(ServiceRegistration $registration) : ServiceRegistration
    {
        $slice = SliceContext::from(context: $this->context);
        if ($slice === '') {
            return $registration;
        }

        if ($registration->metadata->ownerSlice === 'default') {
            $registration->ownedBy(ownerSlice: $slice);
        }

        $category = SliceContext::category(slice: $slice);
        if ($registration->metadata->category === 'configuration' && $category !== '') {
            $registration->category(category: $category);
        }

        if ($registration->metadata->visibility === 'public') {
            $registration->visibility(visibility: SliceContext::defaultVisibility(slice: $slice));
        }

        if ($registration->metadata->reason === 'registered service') {
            $registration->because(reason: "registered through slice view [{$slice}]");
        }

        if ($registration->metadata->provenance === 'manual registration') {
            $registration->provenance(provenance: "slice view [{$slice}]");
        }

        if ($this->strictSliceBoundaries()) {
            $category = SliceContext::category(slice: $slice);
            if ($category !== '') {
                $registration->lockOwnership(ownerSlice: $slice, category: $category);
            }
        }

        return $registration;
    }

    public function defer(string $abstract, mixed $concrete = null) : ServiceRegistration
    {
        return $this->applySliceMetadata(
            registration: $this->base->defer(abstract: $abstract, concrete: $concrete)
        );
    }

    public function singleton(string $abstract, mixed $concrete = null) : ServiceRegistration
    {
        return $this->applySliceMetadata(
            registration: $this->base->singleton(abstract: $abstract, concrete: $concrete)
        );
    }

    public function scoped(string $abstract, mixed $concrete = null) : ServiceRegistration
    {
        return $this->applySliceMetadata(
            registration: $this->base->scoped(abstract: $abstract, concrete: $concrete)
        );
    }

    public function instance(string $abstract, object $instance) : void
    {
        $this->base->instance(abstract: $abstract, instance: $instance);
        $registration = $this->resolver->registrations()->get(abstract: $abstract);
        if ($registration instanceof ServiceRegistration) {
            $this->applySliceMetadata(registration: $registration);
        }
    }

    /**
     * @throws \Throwable
     */
    public function get(string $id) : mixed
    {
        return $this->resolver->getInContext(id: $id, context: $this->context);
    }

    public function extend(string $abstract, callable $closure) : void
    {
        $this->assertOwnedMutation(abstract: $abstract, action: 'extend');
        $this->base->extend(abstract: $abstract, closure: $closure);
    }

    protected function assertOwnedMutation(string $abstract, string $action) : void
    {
        $slice = $this->slice();
        if (! $this->strictSliceBoundaries() || $slice === '') {
            return;
        }

        $registration = $this->resolver->registrations()->get(
            abstract: $this->resolver->registrations()->resolveAlias(abstract: $abstract)
        );
        if (! $registration instanceof ServiceRegistration) {
            throw new InvalidArgumentException(
                message: "Strict slice view [{$slice}] cannot {$action} unknown service [{$abstract}]."
            );
        }

        if ($registration->metadata->ownerSlice !== $slice) {
            throw new InvalidArgumentException(
                message: "Strict slice view [{$slice}] cannot {$action} service [{$abstract}] owned by [{$registration->metadata->ownerSlice}]."
            );
        }
    }

    public function decorate(string $abstract, callable|DecoratorInterface|string $decorator) : void
    {
        $this->assertOwnedMutation(abstract: $abstract, action: 'decorate');
        $this->base->decorate(abstract: $abstract, decorator: $decorator);
    }

    public function when(string $consumer) : RegisterForTarget
    {
        $this->assertGlobalMutationAllowed(action: 'register contextual rules');

        return $this->base->when(consumer: $consumer);
    }

    public function tag(string|array $abstracts, string|array $tags) : void
    {
        foreach ((array) $abstracts as $abstract) {
            if (is_string($abstract) && $abstract !== '') {
                $this->assertOwnedMutation(abstract: $abstract, action: 'tag');
            }
        }

        $this->base->tag(abstracts: $abstracts, tags: $tags);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function forContext(array $context) : ContainerInterface
    {
        if ($context === []) {
            return $this;
        }

        return new self(
            base    : $this->base,
            resolver: $this->resolver,
            context : array_replace($this->context, $context)
        );
    }

    public function forSlice(string $slice) : ContainerInterface
    {
        if (SliceContext::isRoot(slice: $slice)) {
            return new RootCompositionView(base: $this->base, resolver: $this->resolver, context: []);
        }

        $context = SliceContext::with(context: $this->context, slice: $slice);

        return $this->sliceView(context: $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    protected function sliceView(array $context) : ContainerInterface
    {
        return match (SliceContext::category(slice: SliceContext::from(context: $context))) {
            'flow'          => new FlowSliceView(base: $this->base, resolver: $this->resolver, context: $context),
            'capability'    => new CapabilitySliceView(base: $this->base, resolver: $this->resolver, context: $context),
            'configuration' => new ConfigurationSliceView(base: $this->base, resolver: $this->resolver, context: $context),
            'foundation'    => new FoundationSliceView(base: $this->base, resolver: $this->resolver, context: $context),
            default         => new self(base: $this->base, resolver: $this->resolver, context: $context),
        };
    }
}
