<?php

declare(strict_types=1);

namespace Avax\Container;

use Avax\Container\Capabilities\Definitions\Contracts\RegistryInterface;
use Avax\Container\Capabilities\Injection\Reports\InjectionReport;
use Avax\Container\Capabilities\Scopes\ScopeManager;
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

    public function scopes() : ScopeManager;

    public function exportMetrics() : string;
}
