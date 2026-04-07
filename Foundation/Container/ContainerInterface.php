<?php

declare(strict_types=1);

namespace Avax\Container;

use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * Stable public surface for the container component.
 */
interface ContainerInterface extends PsrContainerInterface, RegistryInterface
{
    public function make(string $abstract, array $parameters = []) : object;

    public function call(callable|string $callable, array $parameters = []) : mixed;

    public function injectInto(object $target) : object;

    public function canInject(object $target) : bool;

    public function inspectInjection(object|null $target = null) : InjectionReport;

    public function beginScope() : void;

    public function endScope() : void;

    public function scopes() : ScopeManagerInterface;

    public function exportMetrics() : string;
}
