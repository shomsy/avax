<?php

declare(strict_types=1);

namespace Avax\Container\Features\Core\Contracts;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Features\Core\DTO\InjectionReport;
use Avax\Container\Features\Define\Store\DefinitionStore;

/**
 * Internal container contract for flows that require access to core stores
 * and internal resolution mechanisms (e.g. contextual/recursive resolution).
 */
interface ContainerInternalInterface extends ContainerInterface
{
    public function getDefinitions() : DefinitionStore;

    /**
     * Resolve a service within a specific kernel context (Recursive/Contextual).
     */
    public function resolveContext(KernelContext $context) : mixed;

    /**
     * Get detailed diagnostic information about object injection.
     */
    public function inspectInjection(object $target) : InjectionReport;

    public function exportMetrics() : string;
}
