<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel\Steps;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Core\Kernel\Contracts\KernelStep;
use Avax\Container\Features\Core\Exceptions\ResolutionException;

/**
 * Circular Dependency Step - Detects and blocks recursive service loops.
 *
 * Uses the KernelContext parent chain to identify if the current service
 * is already being resolved in the same dependency path.
 *
 * @see docs/Core/Kernel/Steps/CircularDependencyStep.md#quick-summary
 */
final readonly class CircularDependencyStep implements KernelStep
{
    /**
     * @param KernelContext $context Shared resolution context
     *
     * @throws ResolutionException When a circular dependency is detected
     *
     * @see docs/Core/Kernel/Steps/CircularDependencyStep.md#method-__invokekernelcontext-context-void
     */
    public function __invoke(KernelContext $context) : void
    {
        // Don't check the current service against itself (context->parent is the start of the chain)
        if ($context->parent !== null && $context->parent->contains(serviceId: $context->serviceId)) {
            throw new ResolutionException(
                message: sprintf('Circular dependency detected: %s', $context->getPath())
            );
        }
    }
}
