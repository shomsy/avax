<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\ControlConsistency;

final readonly class EventualConsistency
{
    public function __construct(
        private int $maxConsistencyWindowMs = 1000,
    ) {
    }

    public function isWithinWindow(int $operationTimestamp, int $currentTimestamp): bool
    {
        $diffMs = ($currentTimestamp - $operationTimestamp);

        return $diffMs < $this->maxConsistencyWindowMs;
    }

    public function consistencyDelay(): int
    {
        return $this->maxConsistencyWindowMs;
    }
}
