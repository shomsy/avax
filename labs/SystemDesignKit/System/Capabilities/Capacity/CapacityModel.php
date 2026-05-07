<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Capabilities\Capacity;

/**
 * Capacity model for traffic/storage/cache/queue/latency/availability.
 *
 * Status: @experimental
 *
 * Models whether a system can handle expected traffic, growth,
 * fanout, cache efficiency, queue pressure, and latency requirements.
 */
final readonly class CapacityModel
{
    /**
     * @param array{requests_per_second: int, reads_per_second: int, writes_per_second: int, read_write_ratio: int,
     *                                        peak_multiplier: int} $traffic
     * @param array{growth_per_day: int, average_record_size_bytes: int, retention_days: int}
     *                                                     $storage
     * @param array{hit_ratio_target: float, miss_penalty_ms: int, stampede_protection_required: bool}
     *                                                         $cache
     * @param array{max_depth: int, delay_budget_ms: int, consumer_throughput_per_second: int}
     *                                                $queue
     * @param array{p50_ms: int, p95_ms: int, p99_ms: int}
     *                                             $latency
     * @param array{slo: float, sla: float, failure_budget_minutes_per_month: float}
     *                                            $availability
     */
    public function __construct(
        public array $traffic,
        public array $storage,
        public array $cache,
        public array $queue,
        public array $latency,
        public array $availability,
    ) {}

    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate() : array
    {
        $errors = [];

        if ($this->traffic['requests_per_second'] < 0) {
            $errors[] = 'requests_per_second must be non-negative.';
        }

        if ($this->cache['hit_ratio_target'] < 0 || $this->cache['hit_ratio_target'] > 1) {
            $errors[] = 'cache.hit_ratio_target must be between 0 and 1.';
        }

        if ($this->latency['p50_ms'] > $this->latency['p95_ms']) {
            $errors[] = 'p50 must be <= p95.';
        }

        if ($this->latency['p95_ms'] > $this->latency['p99_ms']) {
            $errors[] = 'p95 must be <= p99.';
        }

        if ($this->availability['slo'] < 0 || $this->availability['slo'] > 100) {
            $errors[] = 'availability.slo must be between 0 and 100.';
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }

    /**
     * Estimate daily storage growth in bytes.
     */
    public function estimatedDailyStorageGrowth() : int
    {
        return $this->storage['growth_per_day'] * $this->storage['average_record_size_bytes'];
    }

    /**
     * Estimate peak requests per second.
     */
    public function estimatedPeakRps() : int
    {
        return (int) ($this->traffic['requests_per_second'] * $this->traffic['peak_multiplier']);
    }
}
