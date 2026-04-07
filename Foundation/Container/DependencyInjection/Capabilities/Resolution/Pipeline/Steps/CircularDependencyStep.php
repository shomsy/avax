<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capabilities\Resolution\Pipeline\Steps;

use Avax\Container\DependencyInjection\Capabilities\Resolution\Errors\ResolutionException;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Kernel\KernelContext;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Pipeline\Contracts\KernelStep;

/**
 * Circular Dependency Step - Detects and blocks recursive service loops.
 *
 * Uses the KernelContext parent chain to identify if the current service
 * is already being resolved in the same dependency path.
 *
 */
final readonly class CircularDependencyStep implements KernelStep
{
    /**
     * @param KernelContext $context Shared resolution context
     *
     * @throws ResolutionException When a circular dependency is detected
     *
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
