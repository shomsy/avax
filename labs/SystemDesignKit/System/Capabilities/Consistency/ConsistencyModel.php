<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Capabilities\Consistency;

use Avax\Labs\SystemDesignKit\System\Capabilities\Consistency\Conflicts\ConflictResolution;
use Avax\Labs\SystemDesignKit\System\Capabilities\Consistency\Conflicts\ConflictStrategy;
use Avax\Labs\SystemDesignKit\System\Capabilities\Consistency\Delivery\DeliveryGuarantee;
use Avax\Labs\SystemDesignKit\System\Capabilities\Consistency\Delivery\DeliverySemantics;
use Avax\Labs\SystemDesignKit\System\Capabilities\Consistency\Lag\ProjectionLag;
use Avax\Labs\SystemDesignKit\System\Capabilities\Consistency\Lag\ReplicationLag;
use Avax\Labs\SystemDesignKit\System\Capabilities\Consistency\Models\ConsistencyModel as ConsistencyModelEnum;
use Avax\Labs\SystemDesignKit\System\Capabilities\Consistency\Models\ConsistencyProfile;
use Avax\Labs\SystemDesignKit\System\Capabilities\Consistency\Staleness\StalenessBudget;

/**
 * Consistency model — aggregates all consistency value objects.
 *
 * @experimental V3 labs
 *
 * Models the consistency guarantees, delivery semantics,
 * staleness budgets, conflict resolution, and replication
 * characteristics of a system architecture.
 */
final readonly class ConsistencyModel
{
    /**
     * @param list<ConsistencyProfile> $profiles
     * @param list<DeliveryGuarantee>  $deliveryGuarantees
     * @param list<StalenessBudget>    $stalenessBudgets
     * @param list<ConflictResolution> $conflictResolutions
     * @param list<ProjectionLag>      $projectionLags
     * @param ReplicationLag|null      $replicationLag
     */
    public function __construct(
        public string          $system,
        public array           $profiles,
        public array           $deliveryGuarantees,
        public array           $stalenessBudgets,
        public array           $conflictResolutions,
        public array           $projectionLags,
        public ?ReplicationLag $replicationLag = null,
    ) {}

    /**
     * Create a ConsistencyModel from config array.
     *
     * @param array<int|string, mixed> $config
     */
    public static function fromConfig(array $config) : self
    {
        $consistency = $config['consistency'] ?? [];

        $profiles         = [];
        $stalenessBudgets = [];

        $writePath = $consistency['write_path'] ?? 'eventual';
        $readPath  = $consistency['read_path'] ?? 'eventual';
        $analytics = $consistency['analytics'] ?? 'eventual';

        $writeModel     = ConsistencyModelEnum::tryFrom($writePath) ?? ConsistencyModelEnum::Eventual;
        $readModel      = ConsistencyModelEnum::tryFrom($readPath) ?? ConsistencyModelEnum::Eventual;
        $analyticsModel = ConsistencyModelEnum::tryFrom($analytics) ?? ConsistencyModelEnum::Eventual;

        $profiles[] = new ConsistencyProfile(
            path                : 'write_path',
            model               : $writeModel,
            stalenessToleranceMs: (int) ($consistency['write_staleness_ms'] ?? ($writeModel === ConsistencyModelEnum::Strong ? 0 : 100)),
        );

        $profiles[] = new ConsistencyProfile(
            path                : 'read_path',
            model               : $readModel,
            stalenessToleranceMs: (int) ($consistency['read_staleness_ms'] ?? ($readModel === ConsistencyModelEnum::Strong ? 0 : 200)),
        );

        $profiles[] = new ConsistencyProfile(
            path                : 'analytics',
            model               : $analyticsModel,
            stalenessToleranceMs: (int) ($consistency['analytics_staleness_ms'] ?? 5000),
        );

        $stalenessBudgets[] = new StalenessBudget(
            toleranceMs: $profiles[0]->stalenessToleranceMs,
            path       : 'write_path',
        );

        $stalenessBudgets[] = new StalenessBudget(
            toleranceMs: $profiles[1]->stalenessToleranceMs,
            path       : 'read_path',
        );

        $stalenessBudgets[] = new StalenessBudget(
            toleranceMs: $profiles[2]->stalenessToleranceMs,
            path       : 'analytics',
        );

        $deliveryGuarantees = [];
        $delivery           = $consistency['delivery'] ?? [];

        if (isset($delivery['semantics'])) {
            $semantics            = DeliverySemantics::tryFrom($delivery['semantics']) ?? DeliverySemantics::AtLeastOnce;
            $deliveryGuarantees[] = new DeliveryGuarantee(
                path               : 'default',
                semantics          : $semantics,
                idempotencyRequired: (bool) ($delivery['idempotency_required'] ?? false),
                dedupRequired      : (bool) ($delivery['dedup_required'] ?? $semantics === DeliverySemantics::AtLeastOnce),
                dedupWindowSeconds : (int) ($delivery['dedup_window_seconds'] ?? 300),
            );
        }

        $conflictResolutions = [];
        $conflicts           = $consistency['conflicts'] ?? [];

        if (isset($conflicts['strategy'])) {
            $strategy              = ConflictStrategy::tryFrom($conflicts['strategy']) ?? ConflictStrategy::LastWriteWins;
            $conflictResolutions[] = new ConflictResolution(
                path                 : 'default',
                strategy             : $strategy,
                estimatedConflictRate: (float) ($conflicts['estimated_rate'] ?? 0.01),
                dataLossAccepted     : (bool) ($conflicts['data_loss_accepted'] ?? false),
            );
        }

        $projectionLags = [];
        $projections    = $consistency['projections'] ?? [];

        if (is_array($projections)) {
            foreach ($projections as $name => $projConfig) {
                if (is_array($projConfig)) {
                    $projectionLags[] = new ProjectionLag(
                        expectedMs: (int) ($projConfig['expected_ms'] ?? 100),
                        p95Ms     : (int) ($projConfig['p95_ms'] ?? 500),
                        p99Ms     : (int) ($projConfig['p99_ms'] ?? 1000),
                        projection: is_string($name) ? $name : 'unknown',
                    );
                }
            }
        }

        $replication    = $consistency['replication'] ?? null;
        $replicationLag = null;

        if (is_array($replication)) {
            $replicationLag = new ReplicationLag(
                expectedMs  : (int) ($replication['expected_ms'] ?? 50),
                p95Ms       : (int) ($replication['p95_ms'] ?? 200),
                p99Ms       : (int) ($replication['p99_ms'] ?? 500),
                replicaCount: (int) ($replication['replica_count'] ?? 2),
                topology    : (string) ($replication['topology'] ?? 'primary-replica'),
            );
        }

        return new self(
            system             : (string) ($config['system'] ?? 'unknown'),
            profiles           : $profiles,
            deliveryGuarantees : $deliveryGuarantees,
            stalenessBudgets   : $stalenessBudgets,
            conflictResolutions: $conflictResolutions,
            projectionLags     : $projectionLags,
            replicationLag     : $replicationLag,
        );
    }

    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate() : array
    {
        $errors = [];

        foreach ($this->profiles as $profile) {
            $result = $profile->validate();
            $errors = array_merge($errors, $result['errors']);
        }

        foreach ($this->deliveryGuarantees as $guarantee) {
            $result = $guarantee->validate();
            $errors = array_merge($errors, $result['errors']);
        }

        foreach ($this->stalenessBudgets as $budget) {
            $result = $budget->validate();
            $errors = array_merge($errors, $result['errors']);
        }

        foreach ($this->conflictResolutions as $resolution) {
            $result = $resolution->validate();
            $errors = array_merge($errors, $result['errors']);
        }

        foreach ($this->projectionLags as $lag) {
            $result = $lag->validate();
            $errors = array_merge($errors, $result['errors']);
        }

        if ($this->replicationLag !== null) {
            $result = $this->replicationLag->validate();
            $errors = array_merge($errors, $result['errors']);
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }

    /**
     * Total estimated latency overhead from consistency models.
     */
    public function totalEstimatedLatencyOverheadMs() : int
    {
        $overhead = 0;

        foreach ($this->profiles as $profile) {
            $overhead += $profile->estimatedLatencyOverheadMs();
        }

        return $overhead;
    }

    /**
     * Paths with high stale-read risk.
     *
     * @return list<string>
     */
    public function highStaleReadRiskPaths() : array
    {
        $paths = [];

        foreach ($this->profiles as $profile) {
            if ($profile->model->staleReadRisk() === 'high') {
                $paths[] = $profile->path;
            }
        }

        return $paths;
    }

    /**
     * Delivery paths with high duplicate risk.
     *
     * @return list<string>
     */
    public function highDuplicateRiskPaths() : array
    {
        $paths = [];

        foreach ($this->deliveryGuarantees as $guarantee) {
            if ($guarantee->duplicateRisk() === 'high') {
                $paths[] = $guarantee->path;
            }
        }

        return $paths;
    }

    /**
     * Conflict paths with data loss risk.
     *
     * @return list<string>
     */
    public function dataLossRiskPaths() : array
    {
        $paths = [];

        foreach ($this->conflictResolutions as $resolution) {
            if ($resolution->strategy->canLoseData()) {
                $paths[] = $resolution->path;
            }
        }

        return $paths;
    }
}
