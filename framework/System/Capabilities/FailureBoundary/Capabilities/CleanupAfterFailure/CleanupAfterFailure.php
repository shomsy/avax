<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\CleanupAfterFailure;

use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureContext;

/**
 * CleanupAfterFailure — Always runs cleanup after a failure boundary execution.
 */
final readonly class CleanupAfterFailure
{
    public function for(FailureContext $context): void
    {
        // Cleanup hooks can be registered here in future versions.
        // For now, this ensures the finally block always executes.
    }
}
