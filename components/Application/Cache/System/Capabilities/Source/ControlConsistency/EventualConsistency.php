<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\ControlConsistency;

enum CacheConsistencyLevel: string
{
    case STRONG           = 'strong';
    case EVENTUAL         = 'eventual';
    case LOCAL            = 'local';
    case READ_YOUR_WRITES = 'read_your_writes';
}

final readonly class EventualConsistency
{
    public function __construct(
        private int $maxConsistencyWindowMs = 1000,
    ) {}

    public function isWithinWindow(int $operationTimestamp, int $currentTimestamp) : bool
    {
        $diffMs = ($currentTimestamp - $operationTimestamp);

        return $diffMs < $this->maxConsistencyWindowMs;
    }

    public function consistencyDelay() : int
    {
        return $this->maxConsistencyWindowMs;
    }
}
