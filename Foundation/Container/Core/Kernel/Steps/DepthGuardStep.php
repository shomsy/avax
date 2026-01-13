<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel\Steps;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Core\Kernel\Contracts\KernelStep;
use Avax\Container\Features\Core\Exceptions\ResolutionException;

/**
 * Depth Guard Step - Prevents Stack Overflow
 *
 * Enforces a maximum resolution depth to prevent stack overflow from
 * deep dependency chains or potential infinite recursion.
 *
 * @see docs/Core/Kernel/Steps/DepthGuardStep.md#quick-summary
 */
final readonly class DepthGuardStep implements KernelStep
{
    private const int DEFAULT_MAX_DEPTH = 64;

    /**
     * @param int $maxDepth Maximum allowed resolution depth
     *
     * @see docs/Core/Kernel/Steps/DepthGuardStep.md#method-__construct
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
     * @see docs/Core/Kernel/Steps/DepthGuardStep.md#method-__invoke
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
