<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Compensation;

use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaState\SagaStep;

/**
 * Result of a compensation execution.
 */
final readonly class CompensationResult
{
    public function __construct(
        public bool    $success,
        public array   $compensatedSteps,
        public array   $failedSteps,
        public ?string $failureReason = null,
    )
    {
    }
}
