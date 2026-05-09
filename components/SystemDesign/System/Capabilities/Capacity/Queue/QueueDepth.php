<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Capabilities\Capacity\Queue;

/**
 * Queue depth model.
 *
 * @experimental V3 labs
 */
final readonly class QueueDepth
{
    public function __construct(
        public int $maxDepth,
        public int $currentDepth = 0,
    ) {}

    public function isNearCapacity(float $threshold = 0.8) : bool
    {
        return $this->utilizationRatio() >= $threshold;
    }

    public function utilizationRatio() : float
    {
        if ($this->maxDepth === 0) {
            return 0.0;
        }

        return $this->currentDepth / $this->maxDepth;
    }

    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate() : array
    {
        $errors = [];

        if ($this->maxDepth <= 0) {
            $errors[] = 'max_depth must be positive.';
        }

        if ($this->currentDepth < 0) {
            $errors[] = 'current_depth must be non-negative.';
        }

        if ($this->currentDepth > $this->maxDepth) {
            $errors[] = 'current_depth exceeds max_depth.';
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }
}
