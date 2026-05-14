<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\CleanupAfterFailure;

use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureContext;

/**
 * CleanupAfterFailure — Executes registered cleanup hooks after a failure boundary execution.
 *
 * This capability guarantees cleanup runs on every execution path:
 * - success
 * - mapped failure
 * - unmapped failure (rethrown)
 * - reporter failure
 * - fallback failure
 *
 * The finally block in RunProtectedAction ensures this capability always executes.
 */
final readonly class CleanupAfterFailure
{
    public function __construct(
        private FailureCleanupRegistry $registry,
    ) {}

    public function for(FailureContext $context): void
    {
        $this->registry->cleanup($context);
    }

    /**
     * Access the underlying cleanup registry for hook registration.
     */
    public function registry() : FailureCleanupRegistry
    {
        return $this->registry;
    }
}
