<?php

declare(strict_types=1);

namespace Avax\Container;

use Avax\Container\Compilation\CompileReport;
use Avax\Container\DependencyInjection\Injection\Reports\InjectionReport;
use Avax\Container\DependencyInjection\Dependencies\Bindings\ServiceRegistryInterface;
use Avax\Container\DependencyInjection\Scopes\ScopeInterface;
use Avax\Container\Observability\RuntimeReport;
use Avax\Container\Runtime\LazyProxy;
use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * Stable public surface for the container component.
 */
interface ContainerInterface extends PsrContainerInterface, ServiceRegistryInterface
{
    public function make(string $abstract, array $parameters = []) : object;

    public function call(callable|string $callable, array $parameters = []) : mixed;

    public function injectInto(object $target) : object;

    public function canInject(object $target) : bool;

    public function inspectInjection(object $target) : InjectionReport;

    public function flush() : void;

    public function reset() : void;

    /**
     * @param array<int, string|\Avax\Container\DependencyInjection\Dependencies\Providers\ServiceProviderInterface> $providers
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

    public function env(string $key, mixed $default = null) : mixed;

    public function openScope() : void;

    public function closeScope() : void;

    /**
     * @param list<string> $serviceIds
     */
    public function compileContainer(array $serviceIds = []) : void;

    /**
     * @param list<string> $serviceIds
     */
    public function warmCompiled(array $serviceIds = []) : void;

    public function flushCompiled() : void;

    /**
     * @param list<string> $serviceIds
     */
    public function rebuildCompiled(array $serviceIds = []) : void;

    /**
     * @param list<string> $serviceIds
     */
    public function compileReport(array $serviceIds = []) : CompileReport|null;

    public function runtimeReport() : RuntimeReport;

    public function hasAlias(string $alias) : bool;

    public function isDeferred(string $id) : bool;

    public function isLazy(string $id) : bool;

    public function isCompiled(string $id) : bool;

    public function isWarmedUp() : bool;

    public function scopes() : ScopeInterface;

    public function tagged(string $tag) : array;

    public function lazy(string $abstract) : LazyProxy;

    public function exportMetrics() : string;

    public function forContext(array $context) : ContainerInterface;
}
