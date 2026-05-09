<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Capabilities\Capacity;

use Avax\Components\SystemDesign\System\Capabilities\Capacity\Availability\FailureBudget;
use Avax\Components\SystemDesign\System\Capabilities\Capacity\Availability\Slo;
use Avax\Components\SystemDesign\System\Capabilities\Capacity\Cache\CacheHitRatio;
use Avax\Components\SystemDesign\System\Capabilities\Capacity\Cache\CacheStampedeRisk;
use Avax\Components\SystemDesign\System\Capabilities\Capacity\Latency\LatencyBudget;
use Avax\Components\SystemDesign\System\Capabilities\Capacity\Queue\ConsumerThroughput;
use Avax\Components\SystemDesign\System\Capabilities\Capacity\Queue\QueueDepth;
use Avax\Components\SystemDesign\System\Capabilities\Capacity\Storage\StorageGrowth;
use Avax\Components\SystemDesign\System\Capabilities\Capacity\Traffic\FanoutSize;
use Avax\Components\SystemDesign\System\Capabilities\Capacity\Traffic\PeakTrafficMultiplier;
use Avax\Components\SystemDesign\System\Capabilities\Capacity\Traffic\RequestsPerSecond;

/**
 * Capacity model — aggregates all capacity value objects.
 *
 * Status: @experimental
 *
 * Models whether a system can handle expected traffic, growth,
 * fanout, cache efficiency, queue pressure, and latency requirements.
 */
final readonly class CapacityModel
{
    public function __construct(
        public string                $system,
        public RequestsPerSecond     $traffic,
        public PeakTrafficMultiplier $peakMultiplier,
        public StorageGrowth         $storage,
        public CacheHitRatio         $cacheHitRatio,
        public CacheStampedeRisk     $cacheStampede,
        public QueueDepth            $queueDepth,
        public ConsumerThroughput    $consumerThroughput,
        public LatencyBudget         $latencyBudget,
        public Slo                   $slo,
        public FailureBudget         $failureBudget,
        public ?FanoutSize           $fanoutSize = null,
    ) {}

    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate() : array
    {
        $errors = [];

        foreach ([
                     $this->traffic->validate(),
                     $this->peakMultiplier->validate(),
                     $this->storage->validate(),
                     $this->cacheHitRatio->validate(),
                     $this->cacheStampede->validate(),
                     $this->queueDepth->validate(),
                     $this->consumerThroughput->validate(),
                     $this->latencyBudget->validate(),
                     $this->slo->validate(),
                     $this->failureBudget->validate(),
                 ] as $result) {
            $errors = array_merge($errors, $result['errors']);
        }

        if ($this->fanoutSize !== null) {
            $fanoutErrors = $this->fanoutSize->validate();
            $errors       = array_merge($errors, $fanoutErrors['errors']);
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }

    /**
     * Estimated peak requests per second.
     */
    public function estimatedPeakRps() : int
    {
        return $this->peakMultiplier->applyTo($this->traffic->total);
    }

    /**
     * Estimated daily storage growth in bytes.
     */
    public function estimatedDailyStorageGrowth() : int
    {
        return $this->storage->dailyGrowthBytes();
    }

    /**
     * Estimated cache hits per second.
     */
    public function estimatedCacheHitsPerSecond() : float
    {
        return $this->cacheHitRatio->cacheHitsPerSecond($this->traffic->total);
    }

    /**
     * Estimated cache misses per second (backend load).
     */
    public function estimatedCacheMissesPerSecond() : float
    {
        return $this->cacheHitRatio->cacheMissesPerSecond($this->traffic->total);
    }

    /**
     * Whether consumer throughput can handle write load.
     */
    public function canHandleWriteLoad() : bool
    {
        return $this->consumerThroughput->totalThroughput() >= $this->traffic->writes;
    }

    /**
     * Required consumer count to handle write load.
     */
    public function requiredConsumerCount() : int
    {
        return $this->consumerThroughput->requiredConsumers($this->traffic->writes);
    }

    /**
     * SLO monthly downtime in human-readable format.
     */
    public function sloMonthlyDowntime() : string
    {
        return $this->slo->monthlyDowntimeHumanReadable();
    }

    /**
     * Create a CapacityModel from parsed config array.
     *
     * @param array<int|string, mixed> $config
     */
    public static function fromConfig(array $config) : self
    {
        $traffic      = $config['traffic'] ?? [];
        $storage      = $config['storage'] ?? [];
        $cache        = $config['cache'] ?? [];
        $queue        = $config['queue'] ?? [];
        $latency      = $config['latency'] ?? [];
        $availability = $config['availability'] ?? [];

        return new self(
            system            : (string) ($config['system'] ?? 'unknown'),
            traffic           : new RequestsPerSecond(
                                    total : (int) ($traffic['requests_per_second'] ?? 0),
                                    reads : (int) ($traffic['reads_per_second'] ?? 0),
                                    writes: (int) ($traffic['writes_per_second'] ?? 0),
                                ),
            peakMultiplier    : new PeakTrafficMultiplier(
                                    multiplier: (int) ($traffic['peak_multiplier'] ?? 1),
                                ),
            storage           : new StorageGrowth(
                                    growthPerDay          : (int) ($storage['growth_per_day'] ?? 0),
                                    averageRecordSizeBytes: (int) ($storage['average_record_size_bytes'] ?? 1),
                                    retentionDays         : (int) ($storage['retention_days'] ?? 365),
                                ),
            cacheHitRatio     : new CacheHitRatio(
                                    target: (float) ($cache['hit_ratio_target'] ?? 0.9),
                                ),
            cacheStampede     : new CacheStampedeRisk(
                                    protectionRequired: (bool) ($cache['stampede_protection_required'] ?? false),
                                ),
            queueDepth        : new QueueDepth(
                                    maxDepth: (int) ($queue['max_depth'] ?? 10000),
                                ),
            consumerThroughput: new ConsumerThroughput(
                                    perSecond: (int) ($queue['consumer_throughput_per_second'] ?? 1000),
                                ),
            latencyBudget     : new LatencyBudget(
                                    p50Ms: (int) ($latency['p50_ms'] ?? 100),
                                    p95Ms: (int) ($latency['p95_ms'] ?? 500),
                                    p99Ms: (int) ($latency['p99_ms'] ?? 1000),
                                ),
            slo               : new Slo(
                                    percentage: (float) ($availability['slo'] ?? 99.9),
                                ),
            failureBudget     : new FailureBudget(
                                    minutesPerMonth: (float) ($availability['failure_budget_minutes_per_month'] ?? 43.8),
                                ),
        );
    }
}
