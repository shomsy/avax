<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Flows\RunProtectedAction;

use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\CleanupAfterFailure\CleanupAfterFailure;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\EnforceTimeout\EnforceTimeout;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\RunFailurePipeline\RunFailurePipeline;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureContext;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailurePipelineResult;
use Closure;
use Throwable;

/**
 * RunProtectedAction — The main flow: wraps an action in a failure boundary.
 *
 * This is the single controlled try/catch/finally that all failure handling flows through.
 * Timeout enforcement happens at the action level via Resilience Timeout.
 */
final readonly class RunProtectedAction
{
    public function __construct(
        private RunFailurePipeline $pipeline,
        private CleanupAfterFailure $cleanup,
        private EnforceTimeout $enforceTimeout,
    ) {
    }

    public function run(Closure $action, FailureContext $context): mixed
    {
        $policy    = $this->pipeline->getPolicy($context);
        $timeoutMs = $policy->timeoutMs;

        try {
            return $this->enforceTimeout->run($action, $timeoutMs);
        } catch (Throwable $failure) {
            $result = $this->pipeline->for($failure, $context, $action);

            // Unwrap FailurePipelineResult to get the actual value
            if ($result instanceof FailurePipelineResult) {
                return $result->value;
            }

            return $result;
        } finally {
            $this->cleanup->for($context);
        }
    }
}
