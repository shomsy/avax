<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Capabilities\FailureSimulation;

use Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\CapacityModel;

/**
 * Simulates a failure scenario and produces a violation report.
 *
 * A failure simulation takes a capacity model, applies a failure mode,
 * and checks whether the system design catches the resulting violation.
 *
 * @experimental V3 labs
 */
final readonly class FailureSimulation
{
    public function __construct(
        public string $name,
        public string $failureMode,
        public string $description,
        /** @var array<int|string, mixed> */
        public array  $parameters,
    ) {}

    /**
     * @param array<int|string, mixed> $config
     */
    public static function fromConfig(array $config) : self
    {
        return new self(
            name       : (string) ($config['name'] ?? 'unknown'),
            failureMode: (string) ($config['failure_mode'] ?? 'unknown'),
            description: (string) ($config['description'] ?? ''),
            parameters : $config['parameters'] ?? [],
        );
    }

    /**
     * Run the failure simulation and produce a violation report.
     *
     * @return array{
     *     simulation: string,
     *     failure_mode: string,
     *     violation_detected: bool,
     *     violation: ?array{
     *         type: string,
     *         severity: string,
     *         description: string,
     *         impact: string,
     *         mitigation: string,
     *     },
     * }
     */
    public function run(CapacityModel $model) : array
    {
        $violation = $this->detectViolation($model);

        return [
            'simulation'       => $this->name,
            'failure_mode'     => $this->failureMode,
            'violation_detected' => $violation !== null,
            'violation'        => $violation,
        ];
    }

    /**
     * @return array{
     *     type: string,
     *     severity: string,
     *     description: string,
     *     impact: string,
     *     mitigation: string,
     * }|null
     */
    private function detectViolation(CapacityModel $model) : ?array
    {
        return match ($this->failureMode) {
            'cache_outage' => $this->simulateCacheOutage($model),
            'queue_flood' => $this->simulateQueueFlood($model),
            'database_slow' => $this->simulateDatabaseSlow($model),
            'traffic_overload' => $this->simulateTrafficOverload($model),
            'replication_lag_spike' => $this->simulateReplicationLagSpike($model),
            'slo_budget_exhausted' => $this->simulateSloBudgetExhausted($model),
            default => null,
        };
    }

    /**
     * @return array{type: string, severity: string, description: string, impact: string, mitigation: string}|null
     */
    private function simulateCacheOutage(CapacityModel $model) : ?array
    {
        if (! $model->cacheStampede->protectionRequired) {
            return [
                'type'        => 'cache_outage_violation',
                'severity'    => 'critical',
                'description' => 'Cache outage without stampede protection causes database thundering herd.',
                'impact'      => 'All traffic hits database; connection exhaustion likely.',
                'mitigation'  => 'Enable cache stampede protection with locking or request coalescing.',
            ];
        }

        $missesPerSecond = $model->estimatedCacheMissesPerSecond();

        if ($missesPerSecond > $model->traffic->writes * 2) {
            return [
                'type'        => 'cache_outage_violation',
                'severity'    => 'high',
                'description' => 'Cache miss rate exceeds write capacity by more than 2x.',
                'impact'      => 'Backend load during cache outage may overwhelm write processors.',
                'mitigation'  => 'Increase cache hit ratio target or add read replicas.',
            ];
        }

        return null;
    }

    /**
     * @return array{type: string, severity: string, description: string, impact: string, mitigation: string}|null
     */
    private function simulateQueueFlood(CapacityModel $model) : ?array
    {
        if (! $model->canHandleWriteLoad()) {
            return [
                'type'        => 'queue_flood_violation',
                'severity'    => 'critical',
                'description' => 'Consumer throughput cannot handle write load; queue will grow unbounded.',
                'impact'      => 'Queue depth will exceed max depth; messages may be dropped or delayed.',
                'mitigation'  => 'Increase consumer count or per-consumer throughput.',
            ];
        }

        $requiredConsumers = $model->requiredConsumerCount();

        if ($requiredConsumers > 100) {
            return [
                'type'        => 'queue_flood_violation',
                'severity'    => 'high',
                'description' => "Requires {$requiredConsumers} consumers to handle load; partition count may be limiting factor.",
                'impact'      => 'High consumer count increases coordination overhead and failure surface.',
                'mitigation'  => 'Increase partition count or improve per-consumer efficiency.',
            ];
        }

        return null;
    }

    /**
     * @return array{type: string, severity: string, description: string, impact: string, mitigation: string}|null
     */
    private function simulateDatabaseSlow(CapacityModel $model) : ?array
    {
        if ($model->latencyBudget->p99Ms > 5000) {
            return [
                'type'        => 'database_slow_violation',
                'severity'    => 'critical',
                'description' => 'P99 latency budget exceeds 5 seconds; database may be a bottleneck.',
                'impact'      => 'User-facing latency will exceed acceptable thresholds.',
                'mitigation'  => 'Add caching layer, optimize queries, or increase read replicas.',
            ];
        }

        if ($model->latencyBudget->p99Ms > $model->latencyBudget->p50Ms * 20) {
            return [
                'type'        => 'database_slow_violation',
                'severity'    => 'high',
                'description' => 'P99/P50 ratio exceeds 20x; tail latency is disproportionately high.',
                'impact'      => 'Worst-case user experience is significantly worse than average.',
                'mitigation'  => 'Investigate tail latency sources: GC pauses, connection pooling, lock contention.',
            ];
        }

        return null;
    }

    /**
     * @return array{type: string, severity: string, description: string, impact: string, mitigation: string}|null
     */
    private function simulateTrafficOverload(CapacityModel $model) : ?array
    {
        $peakRps = $model->estimatedPeakRps();
        $maxQueueDepth = $model->queueDepth->maxDepth;

        // Peak traffic per minute should not exceed queue depth
        if ($peakRps * 60 > $maxQueueDepth * 2) {
            return [
                'type'        => 'traffic_overload_violation',
                'severity'    => 'high',
                'description' => "Peak traffic ({$peakRps} rps) sustained for 60s would exceed queue depth limit ({$maxQueueDepth}).",
                'impact'      => 'Queue may overflow during sustained peak traffic.',
                'mitigation'  => 'Increase queue max depth or add load shedding policy.',
            ];
        }

        if (! $model->canHandleWriteLoad()) {
            return [
                'type'        => 'traffic_overload_violation',
                'severity'    => 'critical',
                'description' => 'Write load at peak exceeds consumer throughput.',
                'impact'      => 'Queue will grow continuously; eventual message loss or extreme latency.',
                'mitigation'  => 'Scale consumers, increase partition count, or reduce write volume.',
            ];
        }

        return null;
    }

    /**
     * @return array{type: string, severity: string, description: string, impact: string, mitigation: string}|null
     */
    private function simulateReplicationLagSpike(CapacityModel $model) : ?array
    {
        if ($model->latencyBudget->p99Ms > 2000) {
            return [
                'type'        => 'replication_lag_violation',
                'severity'    => 'high',
                'description' => 'High P99 latency budget increases replication lag risk.',
                'impact'      => 'Read replicas may serve stale data beyond acceptable staleness budget.',
                'mitigation'  => 'Tighten latency budget or use read-your-writes session consistency.',
            ];
        }

        return null;
    }

    /**
     * @return array{type: string, severity: string, description: string, impact: string, mitigation: string}|null
     */
    private function simulateSloBudgetExhausted(CapacityModel $model) : ?array
    {
        $monthlyDowntimeMinutes = $model->slo->monthlyDowntimeMinutes();

        if ($monthlyDowntimeMinutes < 5) {
            return [
                'type'        => 'slo_budget_tight_violation',
                'severity'    => 'medium',
                'description' => "SLO allows only {$monthlyDowntimeMinutes} minutes of downtime per month.",
                'impact'      => 'Very little error budget; any incident will exhaust SLO.',
                'mitigation'  => 'Improve reliability, add redundancy, or reconsider SLO target.',
            ];
        }

        if ($model->failureBudget->minutesPerMonth > $monthlyDowntimeMinutes) {
            return [
                'type'        => 'slo_budget_exhausted_violation',
                'severity'    => 'critical',
                'description' => 'Failure budget exceeds SLO downtime allowance.',
                'impact'      => 'System cannot meet SLO if it consumes its entire failure budget.',
                'mitigation'  => 'Reduce failure budget or increase SLO allowance.',
            ];
        }

        return null;
    }
}
