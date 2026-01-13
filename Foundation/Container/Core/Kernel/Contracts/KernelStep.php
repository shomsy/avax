<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel\Contracts;

/**
 * Kernel Step Interface
 *
 * Defines the contract for individual steps in the resolution pipeline.
 * Each step processes a KernelContext and contributes to service resolution.
 *
 * @see docs/Core/Kernel/Contracts/KernelStep.md#quick-summary
 */
interface KernelStep
{
    /**
     * Execute this step in the resolution pipeline.
     *
     * Process the resolution context, potentially modifying it with resolved
     * instances, metadata, or error information.
     *
     * @param KernelContext $context The resolution context to process
     *
     * @see docs/Core/Kernel/Contracts/KernelStep.md#method-__invoke
     */
    public function __invoke(KernelContext $context) : void;
}
