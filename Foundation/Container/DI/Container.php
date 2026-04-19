<?php

declare(strict_types=1);

namespace Avax\Container\DI;

use Avax\Container\DI\Capabilities\Composition\Compilation\CompileReport;
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
use Avax\Container\DI\Flows\BootProviders\BootProviders;
use Avax\Container\DI\Flows\CallFunction\CallFunction;
use Avax\Container\DI\Flows\CloseScope\CloseScope;
use Avax\Container\DI\Flows\ExplainService\ExplainService;
use Avax\Container\DI\Flows\ExportGraph\ExportGraph;
use Avax\Container\DI\Flows\OpenScope\OpenScope;
use Avax\Container\DI\Flows\RegisterServices\RegisterServices;
use Avax\Container\DI\Flows\ResolveService\ResolveService;
use Avax\Container\DI\Flows\ValidateComposition\ValidateComposition;
use Closure;
use ReflectionException;
use Throwable;

/**
 * Stable public facade for the container component.
 */
final readonly class Container implements ContainerInterface
{
    private ServiceResolver $resolver;

    public function __construct(ServiceResolver $resolver) { $this->resolver = $resolver; }

    /**
     * @throws Throwable
     */
    public function get(string $id) : mixed
    {
        return $this->resolveService()->get(id: $id);
    }

    private function resolveService() : ResolveService
    {
        return new ResolveService(resolver: $this->resolver);
    }

    public function has(string $id) : bool
    {
        return $this->resolver->has(id: $id);
    }

    public function factory(string $abstract) : Closure
    {
        return fn (array $parameters = []) : object => $this->make(
            abstract  : $abstract,
            parameters: $parameters
        );
    }

    /**
     * @throws Throwable
     */
    public function make(string $abstract, array $parameters = []) : object
    {
        return $this->resolveService()->make(abstract: $abstract, parameters: $parameters);
    }

    public function call(callable|string $callable, array $parameters = []) : mixed
    {
        return $this->callFunction()->call(target: $callable, parameters: $parameters);
    }

    private function callFunction() : CallFunction
    {
        return new CallFunction(resolver: $this->resolver);
    }

    /**
     * @throws ReflectionException
     */
    public function injectInto(object $target) : object
    {
        return $this->resolver->injectInto(target: $target);
    }

    public function flush() : void
    {
        $this->resolver->flush();
    }

    public function reset() : void
    {
        $this->resolver->reset();
    }

    public function instance(string $abstract, object $instance) : void
    {
        $this->registerServices()->instance(abstract: $abstract, instance: $instance);
    }

    private function registerServices() : RegisterServices
    {
        return new RegisterServices(resolver: $this->resolver);
    }

    /**
     * @param array<int, string|ServiceProviderInterface> $providers
     */
    public function bootProviders(array $providers) : void
    {
        (new BootProviders(container: $this))->boot(providers: $providers);
    }

    /**
     * @param list<string> $serviceIds
     *
     * @return list<string>
     * @throws ReflectionException
     */
    public function validate(array $serviceIds = []) : array
    {
        return $this->validateComposition()->validate(serviceIds: $serviceIds);
    }

    private function validateComposition() : ValidateComposition
    {
        return new ValidateComposition(resolver: $this->resolver);
    }

    /**
     * @param string $id
     *
     * @return array<string, mixed>
     * @throws ReflectionException
     */
    public function describeService(string $id) : array
    {
        return $this->explainService()->describe(id: $id);
    }

    private function explainService() : ExplainService
    {
        return new ExplainService(resolver: $this->resolver);
    }

    /**
     * @param string $id
     *
     * @return array<string, mixed>
     * @throws ReflectionException
     */
    public function debugService(string $id) : array
    {
        return $this->explainService()->describe(id: $id);
    }

    /**
     * @param string $id
     *
     * @return array<string, mixed>
     * @throws ReflectionException
     */
    public function debugPlan(string $id) : array
    {
        return $this->explainService()->debugPlan(id: $id);
    }

    /**
     * @param string $id
     *
     * @return array<string, mixed>
     * @throws ReflectionException
     */
    public function debugGraph(string $id = '') : array
    {
        return $this->exportGraphFlow()->debugGraph(id: $id);
    }

    private function exportGraphFlow() : ExportGraph
    {
        return new ExportGraph(resolver: $this->resolver);
    }

    public function debugGovernance(string $id = '') : array
    {
        return $this->explainService()->debugGovernance(id: $id);
    }

    public function debugArchitecture(string $id = '') : array
    {
        return $this->explainService()->debugArchitecture(id: $id);
    }

    public function debugSlice(string $slice = '') : array
    {
        return $this->explainService()->debugSlice(slice: $slice);
    }

    public function debugImports(string $slice = '') : array
    {
        return $this->explainService()->debugImports(slice: $slice);
    }

    /**
     * @throws ReflectionException
     */
    public function debugExports(string $slice = '') : array
    {
        return $this->explainService()->debugExports(slice: $slice);
    }

    public function debugVisibilityViolations(array $serviceIds = []) : array
    {
        return $this->explainService()->debugVisibilityViolations(serviceIds: $serviceIds);
    }

    /**
     * @param string $tag
     *
     * @return array<string, mixed>
     * @throws ReflectionException
     */
    public function debugTags(string $tag) : array
    {
        return $this->explainService()->debugTags(tag: $tag);
    }

    /**
     * @throws ReflectionException
     */
    public function debugGroup(string $group) : array
    {
        return $this->explainService()->debugGroup(group: $group);
    }

    /**
     * @throws ReflectionException
     */
    public function debugSelection(string $id) : array
    {
        return $this->explainService()->debugSelection(id: $id);
    }

    /**
     * @return array<string, string>
     */
    public function debugAliases() : array
    {
        return $this->explainService()->debugAliases();
    }

    /**
     * @return array<string, mixed>
     */
    public function debugScope() : array
    {
        return $this->explainService()->debugScope();
    }

    public function env(string $key, mixed $default = null) : mixed
    {
        return $this->resolver->env(key: $key, default: $default);
    }

    public function openScope(string $kind = ScopeKind::OPERATION, string $scopeId = '') : void
    {
        $this->openScopeFlow()->open(kind: $kind, scopeId: $scopeId);
    }

    private function openScopeFlow() : OpenScope
    {
        return new OpenScope(resolver: $this->resolver);
    }

    public function closeScope(string|null $kind = null) : void
    {
        $this->closeScopeFlow()->close(kind: $kind);
    }

    private function closeScopeFlow() : CloseScope
    {
        return new CloseScope(resolver: $this->resolver);
    }

    /**
     * @throws ReflectionException
     */
    public function compileContainer(array $serviceIds = []) : void
    {
        $this->resolver->compileContainer(serviceIds: $serviceIds);
    }

    /**
     * @throws ReflectionException
     */
    public function warmCompiled(array $serviceIds = []) : void
    {
        $this->resolver->warmCompiled(serviceIds: $serviceIds);
    }

    public function flushCompiled() : void
    {
        $this->resolver->flushCompiled();
    }

    public function rebuildCompiled(array $serviceIds = []) : void
    {
        $this->resolver->rebuildCompiled(serviceIds: $serviceIds);
    }

    public function compileReport(array $serviceIds = []) : CompileReport|null
    {
        return $this->resolver->compileReport(serviceIds: $serviceIds);
    }

    public function runtimeReport() : RuntimeReport
    {
        return $this->resolver->runtimeReport();
    }

    public function hasAlias(string $alias) : bool
    {
        return $this->resolver->hasAlias(alias: $alias);
    }

    public function isDeferred(string $id) : bool
    {
        return $this->resolver->isDeferred(id: $id);
    }

    public function isLazy(string $id) : bool
    {
        return $this->resolver->isLazy(id: $id);
    }

    public function isCompiled(string $id) : bool
    {
        return $this->resolver->isCompiled(id: $id);
    }

    public function isWarmedUp() : bool
    {
        return $this->resolver->isWarmedUp();
    }

    public function canInject(object $target) : bool
    {
        $report = $this->inspectInjection(target: $target);

        return ! empty($report->injectedProperties) || ! empty($report->injectedMethods);
    }

    /**
     * @throws ReflectionException
     */
    public function inspectInjection(object $target) : InjectionReport
    {
        return $this->resolver->inspectInjection(target: $target);
    }

    public function scopes() : ScopeInterface
    {
        return $this->resolver->scopes();
    }

    public function exportMetrics() : string
    {
        return $this->resolver->exportMetrics();
    }

    public function exportGraph(string $format = 'json', string $kind = 'dependency', string $id = '') : string
    {
        return $this->exportGraphFlow()->export(format: $format, kind: $kind, id: $id);
    }

    public function diffGraph(string $format = 'json', string $id = '') : string
    {
        return $this->exportGraphFlow()->diff(format: $format, id: $id);
    }

    /**
     * @throws ReflectionException
     */
    public function why(string $id) : array
    {
        return $this->exportGraphFlow()->why(id: $id);
    }

    public function whoUses(string $id) : array
    {
        return $this->exportGraphFlow()->whoUses(id: $id);
    }

    public function whatBreaksIf(string $id) : array
    {
        return $this->exportGraphFlow()->whatBreaksIf(id: $id);
    }

    /**
     * @throws ReflectionException
     */
    public function showOwner(string $id) : array
    {
        return $this->exportGraphFlow()->showOwner(id: $id);
    }

    public function showSlice(string $slice = '') : array
    {
        return $this->exportGraphFlow()->showSlice(slice: $slice);
    }

    public function alias(string $alias, string $abstract) : void
    {
        $this->registerServices()->alias(alias: $alias, abstract: $abstract);
    }

    public function bind(string $abstract, mixed $concrete = null) : ServiceRegistration
    {
        return $this->registerServices()->bind(abstract: $abstract, concrete: $concrete);
    }

    public function singleton(string $abstract, mixed $concrete = null) : ServiceRegistration
    {
        return $this->registerServices()->singleton(abstract: $abstract, concrete: $concrete);
    }

    public function scoped(string $abstract, mixed $concrete = null) : ServiceRegistration
    {
        return $this->registerServices()->scoped(abstract: $abstract, concrete: $concrete);
    }

    public function when(string $consumer) : RegisterForTarget
    {
        return $this->registerServices()->when(consumer: $consumer);
    }

    public function extend(string $abstract, callable $closure) : void
    {
        $this->registerServices()->extend(abstract: $abstract, closure: $closure);
    }

    public function decorate(string $abstract, callable|DecoratorInterface|string $decorator) : void
    {
        $this->registerServices()->decorate(abstract: $abstract, decorator: $decorator);
    }

    public function tag(string|array $abstracts, string|array $tags) : void
    {
        $this->registerServices()->tag(abstracts: $abstracts, tags: $tags);
    }

    /**
     * @throws Throwable
     */
    public function tagged(string $tag) : array
    {
        return $this->resolver->tagged(tag: $tag);
    }

    /**
     * @throws Throwable
     */
    public function grouped(string $group) : array
    {
        return $this->resolver->grouped(group: $group);
    }

    public function lazy(string $abstract) : LazyProxy
    {
        return $this->resolver->lazy(abstract: $abstract);
    }

    public function defer(string $abstract, mixed $concrete = null) : ServiceRegistration
    {
        return $this->registerServices()->defer(abstract: $abstract, concrete: $concrete);
    }

    public function forContext(array $context) : ContainerInterface
    {
        if ($context === []) {
            return $this;
        }

        return new ContextContainer(base: $this, resolver: $this->resolver, context: $context);
    }

    public function forSlice(string $slice) : ContainerInterface
    {
        if (SliceContext::isRoot(slice: $slice)) {
            return new RootCompositionView(base: $this, resolver: $this->resolver, context: []);
        }

        $context = SliceContext::with(context: [], slice: $slice);
        if ($context === []) {
            return $this;
        }

        return $this->sliceView(context: $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function sliceView(array $context) : ContainerInterface
    {
        return match (SliceContext::category(slice: SliceContext::from(context: $context))) {
            'flow'          => new FlowSliceView(base: $this, resolver: $this->resolver, context: $context),
            'capability'    => new CapabilitySliceView(base: $this, resolver: $this->resolver, context: $context),
            'configuration' => new ConfigurationSliceView(base: $this, resolver: $this->resolver, context: $context),
            'foundation'    => new FoundationSliceView(base: $this, resolver: $this->resolver, context: $context),
            default         => new ContextContainer(base: $this, resolver: $this->resolver, context: $context),
        };
    }
}
