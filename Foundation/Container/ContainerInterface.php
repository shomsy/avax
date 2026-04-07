<?php

declare(strict_types=1);

namespace Avax\Container;

use Avax\Container\DependencyInjection\Injection\InjectionReport;
use Avax\Container\DependencyInjection\Registrations\ServiceRegistryInterface;
use Avax\Container\DependencyInjection\Scopes\ScopeInterface;
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

    public function beginScope() : void;

    public function endScope() : void;

    public function scopes() : ScopeInterface;

    public function exportMetrics() : string;
}
