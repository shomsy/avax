<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\Backpressure;

final readonly class BackpressurePolicy
{
    public function __construct(
        public int   $maxQueueSize = 1000,
        public float $loadThreshold = 0.9,
    ) {}

    public function shouldReject(int $currentQueueSize, float $currentLoad) : bool
    {
        return $currentQueueSize >= $this->maxQueueSize || $currentLoad >= $this->loadThreshold;
    }

    public function shouldDelay(int $currentQueueSize) : bool
    {
        return $currentQueueSize >= (int) ($this->maxQueueSize * 0.8);
    }
}
