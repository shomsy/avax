<?php

declare(strict_types=1);

namespace Avax\Container;

use Avax\Container\DependencyInjection\Injection\Reports\InjectionReport;
use Avax\Container\DependencyInjection\Dependencies\Bindings\ServiceRegistryInterface;
use Avax\Container\DependencyInjection\Scopes\ScopeInterface;
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

    public function scopes() : ScopeInterface;

    public function tagged(string $tag) : array;

    public function lazy(string $abstract) : LazyProxy;

    public function exportMetrics() : string;
}
