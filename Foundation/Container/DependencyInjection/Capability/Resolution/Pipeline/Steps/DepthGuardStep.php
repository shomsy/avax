<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capability\Resolution\Pipeline\Steps;

use Avax\Container\DependencyInjection\Capability\Resolution\Errors\ResolutionException;
use Avax\Container\DependencyInjection\Capability\Resolution\Kernel\KernelContext;
use Avax\Container\DependencyInjection\Capability\Resolution\Pipeline\Contracts\KernelStep;

/**
 * Depth Guard Step - Prevents Stack Overflow
 *
 * Enforces a maximum resolution depth to prevent stack overflow from
 * deep dependency chains or potential infinite recursion.
 *
 */
final readonly class DepthGuardStep implements KernelStep
{
    private const int DEFAULT_MAX_DEPTH = 64;

    /**
     * @param int $maxDepth Maximum allowed resolution depth
     *
     */
    public function __construct(
        private int $maxDepth = self::DEFAULT_MAX_DEPTH
    ) {}

    /**
     * Enforce max depth and throw on overflow.
     *
     *
     * @throws ResolutionException
     *
     */
    public function __invoke(KernelContext $context) : void
    {
        if ($context->depth > $this->maxDepth) {
            throw new ResolutionException(
                message: sprintf(
                    'Resolution depth limit exceeded (%d). Path: %s. Possible circular dependency or excessively deep dependency chain.',
                    $this->maxDepth,
                    $context->getPath()
                )
            );
        }
    }
}
