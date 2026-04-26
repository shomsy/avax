<?php

declare(strict_types=1);

namespace components\Container\DI;

use Closure;
use components\Container\DI\Capabilities\Composition\Compilation\CompileReport;
use components\Container\DI\Capabilities\Declaration\Bindings\ServiceRegistryInterface;
use components\Container\DI\Capabilities\Declaration\Providers\ServiceProviderInterface;
use components\Container\DI\Capabilities\Diagnostics\Errors\ContainerException;
use components\Container\DI\Capabilities\Diagnostics\Errors\ServiceNotFoundException;
use components\Container\DI\Capabilities\Diagnostics\Observability\RuntimeReport;
use components\Container\DI\Capabilities\Execution\Injection\Reports\InjectionReport;
use components\Container\DI\Capabilities\Runtime\LazyProxy;
use components\Container\DI\Capabilities\Runtime\Scopes\ScopeInterface;
use components\Container\DI\Capabilities\Runtime\Scopes\ScopeKind;
use InvalidArgumentException;
use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * Stable public surface for the container component.
 */
interface ContainerInterface extends PsrContainerInterface, ServiceRegistryInterface
{
    /**
     * Builds one object with optional constructor overrides.
     *
     * @param array<string, mixed> $parameters
     *
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function make(string $abstract, array $parameters = []) : object;

    /**
     * Returns a thin factory closure over one container entry.
     *
     * @return Closure(array<string, mixed>=): object
     */
    public function factory(string $abstract) : Closure;

    /**
     * Executes one callable through the container.
     *
     * @param array<string, mixed> $parameters
     *
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function call(callable|string $callable, array $parameters = []) : mixed;

    /**
     * Applies property and method injection to one existing object.
     *
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function injectInto(object $target) : object;

    /**
     * Reports whether one object exposes injectable members.
     */
    public function canInject(object $target) : bool;

    /**
     * Returns the injection report for one object.
     */
    public function inspectInjection(object $target) : InjectionReport;

    /**
     * Clears derived caches, runtime state, and compiled artifacts.
     *
     * Canonical authored registrations stay intact.
     */
    public function flush() : void;

    /**
     * Resets disposable runtime state to a clean boundary.
     *
     * Canonical registrations and compiled artifacts stay intact.
     */
    public function reset() : void;

    /**
     * @param array<int, string|ServiceProviderInterface> $providers
     *
     * @throws InvalidArgumentException
     * @throws ContainerException
     */
    public function bootProviders(array $providers) : void;

    /**
     * @param list<string> $serviceIds
     *
     * @return list<string>
     */
    public function validate(array $serviceIds = []) : array;

    /**
     * @return array<string, mixed>
     */
    public function describeService(string $id) : array;

    /**
     * @return array<string, mixed>
     */
    public function debugService(string $id) : array;

    /**
     * @return array<string, mixed>
     */
    public function debugPlan(string $id) : array;

    /**
     * @return array<string, mixed>
     */
    public function debugGraph(string $id = '') : array;

    /**
     * @return array<string, mixed>
     */
    public function debugGovernance(string $id = '') : array;

    /**
     * @return array<string, mixed>
     */
    public function debugArchitecture(string $id = '') : array;

    /**
     * @return array<string, mixed>
     */
    public function debugSlice(string $slice = '') : array;

    /**
     * @return array<string, mixed>
     */
    public function debugImports(string $slice = '') : array;

    /**
     * @return array<string, mixed>
     */
    public function debugExports(string $slice = '') : array;

    /**
     * @param list<string> $serviceIds
     *
     * @return array<string, mixed>
     */
    public function debugVisibilityViolations(array $serviceIds = []) : array;

    /**
     * @return array<string, mixed>
     */
    public function debugTags(string $tag) : array;

    /**
     * @return array<string, mixed>
     */
    public function debugGroup(string $group) : array;

    /**
     * @return array<string, mixed>
     */
    public function debugSelection(string $id) : array;

    /**
     * @return array<string, string>
     */
    public function debugAliases() : array;

    /**
     * @return array<string, mixed>
     */
    public function debugScope() : array;

    /**
     * Reads one environment-backed setting.
     */
    public function env(string $key, mixed $default = null) : mixed;

    /**
     * Opens one new scope frame.
     *
     * @throws ContainerException
     */
    public function openScope(string $kind = ScopeKind::OPERATION, string $scopeId = '') : void;

    /**
     * Closes the current scope frame.
     *
     * @throws ContainerException
     */
    public function closeScope(string|null $kind = null) : void;

    /**
     * @param list<string> $serviceIds
     *
     * @throws ContainerException
     */
    public function compileContainer(array $serviceIds = []) : void;

    /**
     * @param list<string> $serviceIds
     *
     * @throws ContainerException
     */
    public function warmCompiled(array $serviceIds = []) : void;

    /**
     * Removes the current compiled artifact.
     */
    public function flushCompiled() : void;

    /**
     * @param list<string> $serviceIds
     *
     * @throws ContainerException
     */
    public function rebuildCompiled(array $serviceIds = []) : void;

    /**
     * @param list<string> $serviceIds
     */
    public function compileReport(array $serviceIds = []) : CompileReport|null;

    /**
     * Returns the current runtime state report.
     */
    public function runtimeReport() : RuntimeReport;

    /**
     * Reports whether one alias exists.
     */
    public function hasAlias(string $alias) : bool;

    /**
     * Reports whether one service resolves through deferred ownership.
     */
    public function isDeferred(string $id) : bool;

    /**
     * Reports whether one service has been marked lazy.
     */
    public function isLazy(string $id) : bool;

    /**
     * Reports whether one service is present in the compiled artifact.
     */
    public function isCompiled(string $id) : bool;

    /**
     * Reports whether the compiled runtime is attached or an artifact is available to attach.
     */
    public function isWarmedUp() : bool;

    /**
     * Returns the runtime scope boundary.
     */
    public function scopes() : ScopeInterface;

    /**
     * Resolves every service carrying one tag.
     *
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function tagged(string $tag) : array;

    /**
     * Resolves every service in one ordered group.
     *
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function grouped(string $group) : array;

    /**
     * Returns one lazy proxy for the requested service.
     */
    public function lazy(string $abstract) : LazyProxy;

    /**
     * Exports numeric runtime metrics.
     */
    public function exportMetrics() : string;

    /**
     * Exports one graph artifact in the requested format.
     */
    public function exportGraph(string $format = 'json', string $kind = 'dependency', string $id = '') : string;

    /**
     * Exports one structural graph diff artifact.
     */
    public function diffGraph(string $format = 'json', string $id = '') : string;

    /**
     * @return array<string, mixed>
     */
    public function why(string $id) : array;

    /**
     * @return array<string, mixed>
     */
    public function whoUses(string $id) : array;

    /**
     * @return array<string, mixed>
     */
    public function whatBreaksIf(string $id) : array;

    /**
     * @return array<string, mixed>
     */
    public function showOwner(string $id) : array;

    /**
     * @return array<string, mixed>
     */
    public function showSlice(string $slice = '') : array;

    /**
     * Returns a contextual view over the same container.
     *
     * @param array<string, mixed> $context
     */
    public function forContext(array $context) : self;

    /**
     * Returns one slice-aware view over the same container runtime.
     */
    public function forSlice(string $slice) : self;
}
