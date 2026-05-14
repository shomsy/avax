<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\MemoryGuard;

/**
 * CheckMemoryThreshold — Checks if memory exceeds a soft threshold.
 */
final class CheckMemoryThreshold
{
    /**
     * @param int $thresholdBytes Soft memory threshold in bytes
     */
    public function __construct(
        private int $thresholdBytes = 128 * 1024 * 1024, // 128MB default
    ) {
    }

    public function exceedsThreshold(): bool
    {
        return memory_get_usage() > $this->thresholdBytes;
    }

    /**
     * @param array{memory_bytes: int, memory_mb: float, peak_bytes: int, peak_mb: float} $snapshot
     */
    public function checkWithSnapshot(array $snapshot): bool
    {
        return $snapshot['memory_bytes'] > $this->thresholdBytes;
    }

    public function thresholdBytes(): int
    {
        return $this->thresholdBytes;
    }
}
