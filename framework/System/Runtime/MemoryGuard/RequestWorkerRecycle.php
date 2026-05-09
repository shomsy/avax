<?php

declare(strict_types=1);

namespace Avax\Framework\System\Runtime\MemoryGuard;

/**
 * RequestWorkerRecycle — A decision/finding object requesting graceful worker recycle.
 *
 * This does NOT actually restart the process. It records the decision and reason
 * so that the runtime supervisor (RoadRunner, Swoole, ReactPHP supervisor, etc.)
 * can act on it.
 *
 * Real process reload is future work for RoadRunner/Swoole/FrankenPHP adapters (V4-17).
 */
final readonly class RequestWorkerRecycle
{
    public function __construct(
        public RecycleReason $reason,
        public int $memoryBytes = 0,
        public int $requestCount = 0,
        public string $detail = '',
    ) {
    }

    public function shouldRecycle(): bool
    {
        return true;
    }

    public function description(): string
    {
        $parts = ["Worker recycle requested: {$this->reason->value}"];

        if ($this->memoryBytes > 0) {
            $mb = round($this->memoryBytes / 1024 / 1024, 2);
            $parts[] = "memory={$mb}MB";
        }

        if ($this->requestCount > 0) {
            $parts[] = "requests={$this->requestCount}";
        }

        if ($this->detail !== '') {
            $parts[] = $this->detail;
        }

        return implode(', ', $parts);
    }
}

/**
 * RecycleReason — Why a worker should be recycled.
 */
enum RecycleReason: string
{
    case SoftThresholdExceeded = 'soft_threshold_exceeded';
    case HardThresholdExceeded = 'hard_threshold_exceeded';
    case MaxRequestsExceeded = 'max_requests_exceeded';
}
