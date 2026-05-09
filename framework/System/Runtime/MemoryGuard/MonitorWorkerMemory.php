<?php

declare(strict_types=1);

namespace Avax\Framework\System\Runtime\MemoryGuard;

/**
 * MonitorWorkerMemory — Records and tracks worker memory across requests.
 *
 * Provides before/after/peak memory tracking, delta calculation,
 * and threshold checking with soft/hard limits.
 */
final class MonitorWorkerMemory
{
    private ?int $memoryBefore = null;

    private ?int $memoryAfter = null;

    private int $peakMemory = 0;

    /**
     * @var list<array{memory_bytes: int, memory_mb: float, peak_bytes: int, peak_mb: float}>
     */
    private array $snapshots = [];

    private int $requestCount = 0;

    private ?RequestWorkerRecycle $recycleDecision = null;

    public function __construct(
        private int $softThresholdBytes = 128 * 1024 * 1024, // 128MB
        private int $hardThresholdBytes = 256 * 1024 * 1024, // 256MB
        private int $maxRequests = 0, // 0 = unlimited
    ) {
    }

    /**
     * Record memory before request handling.
     */
    public function captureBefore(): void
    {
        $this->memoryBefore = memory_get_usage();
        $currentPeak = memory_get_peak_usage();
        if ($currentPeak > $this->peakMemory) {
            $this->peakMemory = $currentPeak;
        }
    }

    /**
     * Record memory after request handling.
     */
    public function captureAfter(): void
    {
        $this->memoryAfter = memory_get_usage();
        $currentPeak = memory_get_peak_usage();
        if ($currentPeak > $this->peakMemory) {
            $this->peakMemory = $currentPeak;
        }

        $this->requestCount++;

        // Record snapshot
        $this->snapshots[] = [
            'memory_bytes' => $this->memoryAfter,
            'memory_mb' => round($this->memoryAfter / 1024 / 1024, 2),
            'peak_bytes' => $this->peakMemory,
            'peak_mb' => round($this->peakMemory / 1024 / 1024, 2),
        ];

        // Check thresholds
        $this->evaluateThresholds();
    }

    /**
     * Calculate memory delta between before and after.
     * Returns null if before/after not both captured.
     */
    public function memoryDelta(): ?int
    {
        if ($this->memoryBefore === null || $this->memoryAfter === null) {
            return null;
        }

        return $this->memoryAfter - $this->memoryBefore;
    }

    /**
     * Get the current peak memory usage.
     */
    public function peakMemory(): int
    {
        return $this->peakMemory;
    }

    /**
     * Get the number of requests handled so far.
     */
    public function requestCount(): int
    {
        return $this->requestCount;
    }

    /**
     * Get all recorded snapshots.
     *
     * @return list<array{memory_bytes: int, memory_mb: float, peak_bytes: int, peak_mb: float}>
     */
    public function snapshots(): array
    {
        return $this->snapshots;
    }

    /**
     * Get the latest recycle decision, if any.
     */
    public function recycleDecision(): ?RequestWorkerRecycle
    {
        return $this->recycleDecision;
    }

    /**
     * Get the latest memory snapshot.
     *
     * @return array{memory_bytes: int|null, memory_mb: float|null, peak_bytes: int, peak_mb: float, delta_bytes: int|null, request_count: int}|null
     */
    public function latestSnapshot(): ?array
    {
        if ($this->memoryAfter === null) {
            return null;
        }

        return [
            'memory_bytes' => $this->memoryAfter,
            'memory_mb' => round($this->memoryAfter / 1024 / 1024, 2),
            'peak_bytes' => $this->peakMemory,
            'peak_mb' => round($this->peakMemory / 1024 / 1024, 2),
            'delta_bytes' => $this->memoryDelta(),
            'request_count' => $this->requestCount,
        ];
    }

    /**
     * Evaluate soft and hard memory thresholds.
     */
    private function evaluateThresholds(): void
    {
        $currentMemory = $this->memoryAfter ?? 0;

        // Hard threshold — request immediate recycle
        if ($currentMemory > $this->hardThresholdBytes) {
            $this->recycleDecision = new RequestWorkerRecycle(
                reason: RecycleReason::HardThresholdExceeded,
                memoryBytes: $currentMemory,
                detail: "Memory {$currentMemory} bytes exceeds hard threshold {$this->hardThresholdBytes}",
            );

            return;
        }

        // Soft threshold — warning
        if ($currentMemory > $this->softThresholdBytes) {
            $this->recycleDecision = new RequestWorkerRecycle(
                reason: RecycleReason::SoftThresholdExceeded,
                memoryBytes: $currentMemory,
                detail: "Memory {$currentMemory} bytes exceeds soft threshold {$this->softThresholdBytes}",
            );

            return;
        }

        // Max requests threshold
        if ($this->maxRequests > 0 && $this->requestCount >= $this->maxRequests) {
            $this->recycleDecision = new RequestWorkerRecycle(
                reason: RecycleReason::MaxRequestsExceeded,
                requestCount: $this->requestCount,
                detail: "Request count {$this->requestCount} reached max {$this->maxRequests}",
            );
        }
    }

    /**
     * Clear the recycle decision after it has been acted upon.
     */
    public function clearRecycleDecision(): void
    {
        $this->recycleDecision = null;
    }
}
