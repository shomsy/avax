<?php

declare(strict_types=1);

namespace Avax\Framework\System\Runtime\MemoryGuard;

/**
 * CalculateMemoryGrowthRate — Calculates memory growth rate over multiple snapshots.
 *
 * Uses linear regression slope on the last N snapshots to determine if
 * memory is growing steadily (potential leak) or stable.
 */
final readonly class CalculateMemoryGrowthRate
{
    /**
     * Calculate the memory growth rate in bytes per request.
     *
     * @param list<array{memory_bytes: int, memory_mb: float, peak_bytes: int, peak_mb: float}> $snapshots
     * @param int|null $window Use only the last N snapshots. Null = all.
     * @return float|null Growth rate in bytes per request. Null if fewer than 2 snapshots.
     */
    public function calculate(array $snapshots, int|null $window = null) : float|null
    {
        if ($window !== null && count($snapshots) > $window) {
            $snapshots = array_slice($snapshots, -$window);
        }

        $count = count($snapshots);
        if ($count < 2) {
            return null;
        }

        // Simple linear regression: slope = (n*sum(xy) - sum(x)*sum(y)) / (n*sum(x^2) - (sum(x))^2)
        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumX2 = 0;

        foreach ($snapshots as $i => $snapshot) {
            $x = $i;
            $y = $snapshot['memory_bytes'];
            $sumX += $x;
            $sumY += $y;
            $sumXY += $x * $y;
            $sumX2 += $x * $x;
        }

        $denominator = ($count * $sumX2) - ($sumX * $sumX);
        if ($denominator === 0) {
            return 0.0;
        }

        return (($count * $sumXY) - ($sumX * $sumY)) / $denominator;
    }

    /**
     * Determine if the growth rate indicates a potential memory leak.
     *
     * @param list<array{memory_bytes: int, memory_mb: float, peak_bytes: int, peak_mb: float}> $snapshots
     * @param int $thresholdBytesPerRequest Growth rate threshold in bytes per request
     * @return bool True if growth rate exceeds threshold (potential leak)
     */
    public function isPotentialLeak(array $snapshots, int $thresholdBytesPerRequest = 1024, int|null $window = null) : bool
    {
        $rate = $this->calculate($snapshots, $window);
        if ($rate === null) {
            return false;
        }

        return $rate > $thresholdBytesPerRequest;
    }
}
