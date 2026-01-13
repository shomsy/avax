<?php

declare(strict_types=1);

namespace Avax\Container\Features\Actions\Resolve\Contracts;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Features\Core\Contracts\ContainerInterface;

/**
 * Interface for dependency resolution logic.
 *
 * @see docs/Features/Actions/Resolve/Contracts/DependencyResolverInterface.md#quick-summary
 */
interface DependencyResolverInterface
{
    /**
     * Resolves a list of method or constructor parameters.
     *
     * @see docs/Features/Actions/Resolve/Contracts/DependencyResolverInterface.md#method-resolveparameters
     */
    public function resolveParameters(
        array              $parameters,
        array              $overrides,
        ContainerInterface $container,
        KernelContext|null $context
    ) : array;
}
