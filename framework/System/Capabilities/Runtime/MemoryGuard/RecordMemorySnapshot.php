<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\MemoryGuard;

/**
 * RecordMemorySnapshot — Records a simple memory snapshot.
 */
final readonly class RecordMemorySnapshot
{
    /**
     * @return array{memory_bytes: int, memory_mb: float, peak_bytes: int, peak_mb: float}
     */
    public function record(): array
    {
        $bytes = memory_get_usage();
        $peak = memory_get_peak_usage();

        return [
            'memory_bytes' => $bytes,
            'memory_mb' => round($bytes / 1024 / 1024, 2),
            'peak_bytes' => $peak,
            'peak_mb' => round($peak / 1024 / 1024, 2),
        ];
    }
}
