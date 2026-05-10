<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Flows\EnforceBackpressure;

use Avax\Components\Operations\Resilience\System\Capabilities\Backpressure\BackpressurePolicy;
use Avax\Components\Operations\Resilience\System\Foundation\Failure\BackpressureFailure;

/**
 * EnforceBackpressure — actually gates work when system thresholds are exceeded.
 *
 * Unlike BackpressurePolicy which only calculates whether to reject/delay,
 * this flow enforces the decision by throwing BackpressureFailure when
 * the system is overloaded.
 */
final readonly class EnforceBackpressure
{
    public function __construct(
        private BackpressurePolicy $policy,
    ) {}

    /**
     * Enforce backpressure against current system state.
     *
     * @throws BackpressureFailure when work should be rejected
     */
    public function execute(int $currentQueueSize, float $currentLoad = 0.0) : void
    {
        if ($this->policy->shouldReject($currentQueueSize, $currentLoad)) {
            throw new BackpressureFailure(
                sprintf(
                    'Backpressure enforced: queue size %d (max %d), load %.2f (threshold %.2f)',
                    $currentQueueSize,
                    $this->policy->maxQueueSize,
                    $currentLoad,
                    $this->policy->loadThreshold,
                ),
            );
        }
    }

    /**
     * Check if work should be delayed (not rejected).
     *
     * Returns delay recommendation without throwing.
     *
     * @return array{should_delay: bool, suggested_delay_ms: int}
     */
    public function checkDelay(int $currentQueueSize) : array
    {
        $shouldDelay = $this->policy->shouldDelay($currentQueueSize);
        $ratio = $currentQueueSize / max(1, $this->policy->maxQueueSize);

        // Scale delay from 0ms to 1000ms based on queue saturation
        $suggestedDelayMs = (int) ($ratio * 1000);

        return [
            'should_delay' => $shouldDelay,
            'suggested_delay_ms' => min($suggestedDelayMs, 1000),
        ];
    }
}
