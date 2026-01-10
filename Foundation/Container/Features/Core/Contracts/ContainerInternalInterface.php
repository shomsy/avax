<?php

declare(strict_types=1);

namespace Avax\Container\Features\Core\Contracts;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Features\Core\DTO\InjectionReport;
use Avax\Container\Features\Define\Store\DefinitionStore;

/**
 * Internal Container Operations Interface
 *
 * Extends the public container interface with internal operations required by
 * the resolution engine and kernel components. Provides access to core stores,
 * contextual resolution mechanisms, and diagnostic capabilities that should not
 * be exposed to application code.
 *
 * This interface is used internally by the container kernel and should not be
 * implemented or used directly by application code.
 *
 * @package Avax\Container\Features\Core\Contracts
 * @see docs/Features/Core/Contracts/ContainerInternalInterface.md
 */
interface ContainerInternalInterface extends ContainerInterface
{
    public function getDefinitions(): DefinitionStore;

    /**
     * Resolve a service within a specific kernel context (Recursive/Contextual).
     */
    public function resolveContext(KernelContext $context): mixed;

    /**
     * Get detailed diagnostic information about object injection.
     */
    public function inspectInjection(object $target): InjectionReport;

    public function exportMetrics(): string;
}
