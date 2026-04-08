<?php

declare(strict_types=1);

namespace Avax\Container;

use Avax\Container\Compilation\CompileReport;
use Avax\Container\DependencyInjection\Dependencies\Bindings\DecoratorInterface;
use Avax\Container\DependencyInjection\Dependencies\Providers\ServiceProviderInterface;
use Avax\Container\DependencyInjection\Injection\Reports\InjectionReport;
use Avax\Container\DependencyInjection\Dependencies\Bindings\ServiceRegistryInterface;
use Avax\Container\Errors\ContainerException;
use Avax\Container\Errors\ServiceNotFoundException;
use Avax\Container\DependencyInjection\Scopes\ScopeInterface;
use Avax\Container\Observability\RuntimeReport;
use Avax\Container\Runtime\LazyProxy;
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
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function make(string $abstract, array $parameters = []) : object;

    /**
     * Executes one callable through the container.
     *
     * @param array<string, mixed> $parameters
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
     * Clears user registrations, runtime state, and compiled artifacts.
     */
    public function flush() : void;

    /**
     * Resets the container runtime to a clean state.
     */
    public function reset() : void;

    /**
     * @param array<int, string|ServiceProviderInterface> $providers
     * @throws InvalidArgumentException
     * @throws ContainerException
     */
    public function bootProviders(array $providers) : void;

    /**
     * @param list<string> $serviceIds
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
    public function debugTags(string $tag) : array;

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
    public function openScope() : void;

    /**
     * Closes the current scope frame.
     *
     * @throws ContainerException
     */
    public function closeScope() : void;

    /**
     * @param list<string> $serviceIds
     * @throws ContainerException
     */
    public function compileContainer(array $serviceIds = []) : void;

    /**
     * @param list<string> $serviceIds
     * @throws ContainerException
     */
    public function warmCompiled(array $serviceIds = []) : void;

    /**
     * Removes the current compiled artifact.
     */
    public function flushCompiled() : void;

    /**
     * @param list<string> $serviceIds
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
     * Reports whether the compiled artifact is attached and ready.
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
     * Returns one lazy proxy for the requested service.
     */
    public function lazy(string $abstract) : LazyProxy;

    /**
     * Exports numeric runtime metrics.
     */
    public function exportMetrics() : string;

    /**
     * Returns a contextual view over the same container.
     *
     * @param array<string, mixed> $context
     */
    public function forContext(array $context) : ContainerInterface;
}
