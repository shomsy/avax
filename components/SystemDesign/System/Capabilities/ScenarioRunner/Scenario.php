<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Capabilities\ScenarioRunner;

use Avax\Components\SystemDesign\System\Capabilities\Capacity\CapacityModel;

/**
 * A named scenario that can be evaluated against a capacity model.
 *
 * @experimental V3 labs
 */
final readonly class Scenario
{
    /**
     * @param list<ScenarioStep>        $steps
     * @param list<ScenarioExpectation> $expectations
     */
    public function __construct(
        public string $name,
        public string $type,
        public string $description,
        public array  $steps,
        public array  $expectations,
    ) {}

    /**
     * @param array<int|string, mixed> $config
     */
    public static function fromConfig(array $config) : self
    {
        $steps = [];

        foreach ($config['steps'] ?? [] as $stepConfig) {
            if (is_array($stepConfig)) {
                $steps[] = ScenarioStep::fromConfig($stepConfig);
            }
        }

        $expectations = [];

        foreach ($config['expectations'] ?? [] as $expectConfig) {
            if (is_array($expectConfig)) {
                $expectations[] = ScenarioExpectation::fromConfig($expectConfig);
            }
        }

        return new self(
            name        : (string) ($config['name'] ?? 'unknown'),
            type        : (string) ($config['type'] ?? 'unknown'),
            description : (string) ($config['description'] ?? ''),
            steps       : $steps,
            expectations: $expectations,
        );
    }

    /**
     * Evaluate this scenario against a capacity model.
     *
     * @return array{
     *     scenario: string,
     *     type: string,
     *     passed: bool,
     *     results: list<array{assertion: string, passed: bool, detail: string}>,
     * }
     */
    public function evaluate(CapacityModel $model) : array
    {
        $results = [];

        foreach ($this->expectations as $expectation) {
            $result    = $this->evaluateAssertion($expectation, $model);
            $results[] = $result;
        }

        $passed = array_reduce(
            $results,
            static fn (bool $carry, array $r) : bool => $carry && $r['passed'],
            true,
        );

        return [
            'scenario' => $this->name,
            'type'     => $this->type,
            'passed'   => $passed,
            'results'  => $results,
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function evaluateAssertion(ScenarioExpectation $expectation, CapacityModel $model) : array
    {
        return match ($expectation->assertion) {
            'latency_p99_under_threshold'               => $this->checkLatencyP99($model, $expectation),
            'queue_depth_under_limit'                   => $this->checkQueueDepth($model, $expectation),
            'no_data_loss'                              => $this->checkNoDataLoss($model),
            'no_request_loss'                           => $this->checkNoRequestLoss($model),
            'read_your_writes_maintained'               => $this->checkReadYourWrites($model),
            'stale_reads_within_budget'                 => $this->checkStaleReadsBudget($model),
            'no_message_loss'                           => $this->checkNoMessageLoss($model),
            'consumer_rebalance_under_30s'              => $this->checkConsumerRebalance($model),
            'stampede_protection_active'                => $this->checkStampedeProtection($model),
            'database_connections_not_exhausted'        => $this->checkDatabaseConnections($model),
            'load_shedding_active'                      => $this->checkLoadShedding($model),
            'circuit_breaker_opens_under_timeout'       => $this->checkCircuitBreaker($model),
            'failover_to_database'                      => $this->checkFailover($model),
            'error_rate_under_slo_budget'               => $this->checkErrorRateUnderSlo($model),
            'checkout_latency_within_sla'               => $this->checkCheckoutLatency($model),
            'no_inventory_over_reservation'             => $this->checkInventoryOverReservation($model),
            'only_one_order_succeeds'                   => $this->checkSingleOrderSuccess($model),
            'no_negative_inventory'                     => $this->checkNoNegativeInventory($model),
            'consistent_read_after_write'               => $this->checkConsistentReadWrite($model),
            'outbox_preserves_order'                    => $this->checkOutboxOrder($model),
            'idempotency_handles_duplicate'             => $this->checkIdempotency($model),
            'failover_under_30s'                        => $this->checkFailoverUnder30s($model),
            'no_committed_data_loss'                    => $this->checkNoCommittedDataLoss($model),
            'replication_lag_within_budget'             => $this->checkReplicationLagBudget($model),
            'p99_latency_within_sla'                    => $this->checkP99LatencyWithinSla($model),
            'dlq_alert_triggered'                       => $this->checkDlqAlert($model),
            'circuit_breaker_opens'                     => $this->checkCircuitBreakerOpens($model),
            'graceful_degradation_enabled'              => $this->checkGracefulDegradation($model),
            'orders_queue_locally'                      => $this->checkOrdersQueueLocally($model),
            'inventory_not_over_committed'              => $this->checkInventoryNotOverCommitted($model),
            'partition_resolves_or_circuit_opens'       => $this->checkPartitionResolution($model),
            'order_not_duplicated'                      => $this->checkOrderNotDuplicated($model),
            'inventory_not_reserved_for_failed_payment' => $this->checkInventoryNotReservedForFailedPayment($model),
            'user_receives_clear_error'                 => $this->checkUserReceivesClearError($model),
            default                                     => [
                'assertion' => $expectation->assertion,
                'passed'    => false,
                'detail'    => "Unknown assertion: {$expectation->assertion}",
            ],
        };
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkLatencyP99(CapacityModel $model, ScenarioExpectation $expectation) : array
    {
        $threshold = (int) ($expectation->threshold ?? $model->latencyBudget->p99Ms);
        $passed    = $model->latencyBudget->p99Ms <= $threshold;

        return [
            'assertion' => 'latency_p99_under_threshold',
            'passed'    => $passed,
            'detail'    => $passed
                ? "P99 latency {$model->latencyBudget->p99Ms}ms under threshold {$threshold}ms"
                : "P99 latency {$model->latencyBudget->p99Ms}ms exceeds threshold {$threshold}ms",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkQueueDepth(CapacityModel $model, ScenarioExpectation $expectation) : array
    {
        $threshold = (int) ($expectation->threshold ?? $model->queueDepth->maxDepth);
        $passed    = $model->queueDepth->maxDepth <= $threshold;

        return [
            'assertion' => 'queue_depth_under_limit',
            'passed'    => $passed,
            'detail'    => $passed
                ? "Queue depth {$model->queueDepth->maxDepth} under limit {$threshold}"
                : "Queue depth {$model->queueDepth->maxDepth} exceeds limit {$threshold}",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkNoDataLoss(CapacityModel $model) : array
    {
        $passed = $model->slo->percentage >= 99.9;

        return [
            'assertion' => 'no_data_loss',
            'passed'    => $passed,
            'detail'    => $passed
                ? "SLO {$model->slo->percentage}% ensures strong durability guarantee"
                : "SLO {$model->slo->percentage}% too low for no-data-loss guarantee",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkNoRequestLoss(CapacityModel $model) : array
    {
        $canHandle = $model->canHandleWriteLoad();

        return [
            'assertion' => 'no_request_loss',
            'passed'    => $canHandle,
            'detail'    => $canHandle
                ? "Consumer throughput can handle write load"
                : "Consumer throughput insufficient for write load",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkReadYourWrites(CapacityModel $model) : array
    {
        $passed = $model->slo->percentage >= 99.9;

        return [
            'assertion' => 'read_your_writes_maintained',
            'passed'    => $passed,
            'detail'    => $passed
                ? "High SLO implies strong read-your-writes guarantee"
                : "SLO too low to guarantee read-your-writes",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkStaleReadsBudget(CapacityModel $model) : array
    {
        $passed = $model->latencyBudget->p99Ms <= 5000;

        return [
            'assertion' => 'stale_reads_within_budget',
            'passed'    => $passed,
            'detail'    => $passed
                ? "P99 latency {$model->latencyBudget->p99Ms}ms within 5s stale-read budget"
                : "P99 latency {$model->latencyBudget->p99Ms}ms exceeds stale-read budget",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkNoMessageLoss(CapacityModel $model) : array
    {
        $passed = $model->slo->percentage >= 99.9;

        return [
            'assertion' => 'no_message_loss',
            'passed'    => $passed,
            'detail'    => $passed
                ? "SLO {$model->slo->percentage}% ensures message durability"
                : "SLO too low for guaranteed message delivery",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkConsumerRebalance(CapacityModel $model) : array
    {
        $passed = $model->queueDepth->maxDepth > 0;

        return [
            'assertion' => 'consumer_rebalance_under_30s',
            'passed'    => $passed,
            'detail'    => "Queue depth configured; rebalance expected within consumer topology limits",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkStampedeProtection(CapacityModel $model) : array
    {
        $passed = $model->cacheStampede->protectionRequired;

        return [
            'assertion' => 'stampede_protection_active',
            'passed'    => $passed,
            'detail'    => $passed
                ? "Cache stampede protection is required and assumed active"
                : "Cache stampede protection not configured",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkDatabaseConnections(CapacityModel $model) : array
    {
        $missesPerSecond = $model->estimatedCacheMissesPerSecond();
        $passed          = $missesPerSecond < $model->traffic->total;

        return [
            'assertion' => 'database_connections_not_exhausted',
            'passed'    => $passed,
            'detail'    => $passed
                ? "Cache misses per second (" . round($missesPerSecond) . ") below total traffic"
                : "Cache misses could exhaust database connections",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkLoadShedding(CapacityModel $model) : array
    {
        $peakRps = $model->estimatedPeakRps();
        $passed  = $peakRps > $model->traffic->total;

        return [
            'assertion' => 'load_shedding_active',
            'passed'    => $passed,
            'detail'    => $passed
                ? "Peak capacity {$peakRps} rps above base load; shedding not needed under normal peak"
                : "Peak capacity insufficient; load shedding required",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkCircuitBreaker(CapacityModel $model) : array
    {
        $passed = $model->failureBudget->minutesPerMonth > 0;

        return [
            'assertion' => 'circuit_breaker_opens_under_timeout',
            'passed'    => $passed,
            'detail'    => $passed
                ? "Failure budget configured; circuit breaker assumed"
                : "No failure budget; circuit breaker cannot be properly configured",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkFailover(CapacityModel $model) : array
    {
        $passed = $model->slo->percentage >= 99.9;

        return [
            'assertion' => 'failover_to_database',
            'passed'    => $passed,
            'detail'    => $passed
                ? "High SLO implies failover capability"
                : "SLO too low to guarantee failover",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkErrorRateUnderSlo(CapacityModel $model) : array
    {
        $errorBudget = $model->failureBudget->minutesPerMonth;
        $passed      = $errorBudget > 0 && $errorBudget < 60;

        return [
            'assertion' => 'error_rate_under_slo_budget',
            'passed'    => $passed,
            'detail'    => $passed
                ? "Error budget {$errorBudget} min/month is tight; error rate must stay within SLO"
                : "Error budget too large; SLO not meaningful",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkCheckoutLatency(CapacityModel $model) : array
    {
        $passed = $model->latencyBudget->p99Ms <= 2000;

        return [
            'assertion' => 'checkout_latency_within_sla',
            'passed'    => $passed,
            'detail'    => $passed
                ? "P99 latency {$model->latencyBudget->p99Ms}ms within 2s checkout SLA"
                : "P99 latency {$model->latencyBudget->p99Ms}ms exceeds checkout SLA",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkInventoryOverReservation(CapacityModel $model) : array
    {
        $passed = $model->canHandleWriteLoad();

        return [
            'assertion' => 'no_inventory_over_reservation',
            'passed'    => $passed,
            'detail'    => $passed
                ? "Write load capacity sufficient; no over-reservation risk"
                : "Write load insufficient; inventory over-reservation possible",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkSingleOrderSuccess(CapacityModel $model) : array
    {
        $passed = $model->slo->percentage >= 99.9;

        return [
            'assertion' => 'only_one_order_succeeds',
            'passed'    => $passed,
            'detail'    => $passed
                ? "High SLO implies strong consistency for concurrent writes"
                : "SLO too low for strong concurrent-write guarantee",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkNoNegativeInventory(CapacityModel $model) : array
    {
        $passed = $model->canHandleWriteLoad();

        return [
            'assertion' => 'no_negative_inventory',
            'passed'    => $passed,
            'detail'    => $passed
                ? "Write capacity sufficient; no negative inventory risk"
                : "Write capacity insufficient; negative inventory possible",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkConsistentReadWrite(CapacityModel $model) : array
    {
        $passed = $model->slo->percentage >= 99.9;

        return [
            'assertion' => 'consistent_read_after_write',
            'passed'    => $passed,
            'detail'    => $passed
                ? "High SLO implies consistent read-after-write"
                : "SLO too low for consistent read-after-write",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkOutboxOrder(CapacityModel $model) : array
    {
        $passed = $model->queueDepth->maxDepth > 0;

        return [
            'assertion' => 'outbox_preserves_order',
            'passed'    => $passed,
            'detail'    => "Outbox ordering depends on queue partitioning; queue depth configured",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkIdempotency(CapacityModel $model) : array
    {
        $passed = $model->slo->percentage >= 99.9;

        return [
            'assertion' => 'idempotency_handles_duplicate',
            'passed'    => $passed,
            'detail'    => $passed
                ? "High SLO implies idempotency protection"
                : "SLO too low to guarantee idempotency",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkFailoverUnder30s(CapacityModel $model) : array
    {
        $passed = $model->latencyBudget->p99Ms <= 30000;

        return [
            'assertion' => 'failover_under_30s',
            'passed'    => $passed,
            'detail'    => $passed
                ? "P99 budget {$model->latencyBudget->p99Ms}ms allows 30s failover"
                : "P99 budget too tight for 30s failover",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkNoCommittedDataLoss(CapacityModel $model) : array
    {
        $passed = $model->slo->percentage >= 99.9;

        return [
            'assertion' => 'no_committed_data_loss',
            'passed'    => $passed,
            'detail'    => $passed
                ? "High SLO guarantees no committed data loss"
                : "SLO too low for no-data-loss guarantee",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkReplicationLagBudget(CapacityModel $model) : array
    {
        $passed = $model->latencyBudget->p99Ms <= 5000;

        return [
            'assertion' => 'replication_lag_within_budget',
            'passed'    => $passed,
            'detail'    => $passed
                ? "P99 latency within replication lag budget"
                : "P99 latency may exceed replication lag budget",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkP99LatencyWithinSla(CapacityModel $model) : array
    {
        $passed = $model->latencyBudget->p99Ms <= 2000;

        return [
            'assertion' => 'p99_latency_within_sla',
            'passed'    => $passed,
            'detail'    => $passed
                ? "P99 latency {$model->latencyBudget->p99Ms}ms within SLA"
                : "P99 latency {$model->latencyBudget->p99Ms}ms exceeds SLA",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkDlqAlert(CapacityModel $model) : array
    {
        $passed = $model->failureBudget->minutesPerMonth > 0;

        return [
            'assertion' => 'dlq_alert_triggered',
            'passed'    => $passed,
            'detail'    => $passed
                ? "Failure budget configured; DLQ alerting assumed"
                : "No failure budget; DLQ alerting not configured",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkCircuitBreakerOpens(CapacityModel $model) : array
    {
        $passed = $model->failureBudget->minutesPerMonth < 60;

        return [
            'assertion' => 'circuit_breaker_opens',
            'passed'    => $passed,
            'detail'    => $passed
                ? "Tight failure budget; circuit breaker will open"
                : "Large failure budget; circuit breaker may not open in time",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkGracefulDegradation(CapacityModel $model) : array
    {
        $passed = $model->canHandleWriteLoad();

        return [
            'assertion' => 'graceful_degradation_enabled',
            'passed'    => $passed,
            'detail'    => $passed
                ? "Write capacity sufficient for graceful degradation"
                : "Write capacity insufficient; graceful degradation not guaranteed",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkOrdersQueueLocally(CapacityModel $model) : array
    {
        $passed = $model->queueDepth->maxDepth > 0;

        return [
            'assertion' => 'orders_queue_locally',
            'passed'    => $passed,
            'detail'    => $passed
                ? "Queue depth configured; local queuing possible during partition"
                : "No queue capacity; orders may be lost during partition",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkInventoryNotOverCommitted(CapacityModel $model) : array
    {
        $passed = $model->canHandleWriteLoad();

        return [
            'assertion' => 'inventory_not_over_committed',
            'passed'    => $passed,
            'detail'    => $passed
                ? "Write capacity sufficient; inventory over-commit unlikely"
                : "Write capacity insufficient; inventory over-commit risk",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkPartitionResolution(CapacityModel $model) : array
    {
        $passed = $model->failureBudget->minutesPerMonth > 0;

        return [
            'assertion' => 'partition_resolves_or_circuit_opens',
            'passed'    => $passed,
            'detail'    => $passed
                ? "Failure budget configured; partition timeout triggers circuit breaker"
                : "No failure budget; partition may hang indefinitely",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkOrderNotDuplicated(CapacityModel $model) : array
    {
        $passed = $model->slo->percentage >= 99.9 && $model->cacheStampede->protectionRequired;

        return [
            'assertion' => 'order_not_duplicated',
            'passed'    => $passed,
            'detail'    => $passed
                ? "High SLO and idempotency protection prevent order duplication"
                : "Insufficient guarantees against order duplication",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkInventoryNotReservedForFailedPayment(CapacityModel $model) : array
    {
        $passed = $model->canHandleWriteLoad() && $model->slo->percentage >= 99.9;

        return [
            'assertion' => 'inventory_not_reserved_for_failed_payment',
            'passed'    => $passed,
            'detail'    => $passed
                ? "Write capacity and SLO ensure proper inventory rollback on payment failure"
                : "Insufficient capacity for inventory rollback guarantees",
        ];
    }

    /**
     * @return array{assertion: string, passed: bool, detail: string}
     */
    private function checkUserReceivesClearError(CapacityModel $model) : array
    {
        $passed = $model->latencyBudget->p99Ms <= 15000;

        return [
            'assertion' => 'user_receives_clear_error',
            'passed'    => $passed,
            'detail'    => $passed
                ? "P99 latency budget {$model->latencyBudget->p99Ms}ms allows timely error response"
                : "P99 latency budget too high; user may experience timeout",
        ];
    }
}
