<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\SystemDesign\Consistency;

use Avax\Components\SystemDesign\System\Capabilities\Consistency\Conflicts\ConflictResolution;
use Avax\Components\SystemDesign\System\Capabilities\Consistency\Conflicts\ConflictStrategy;
use Avax\Components\SystemDesign\System\Capabilities\Consistency\ConsistencyModel;
use Avax\Components\SystemDesign\System\Capabilities\Consistency\Delivery\DeliveryGuarantee;
use Avax\Components\SystemDesign\System\Capabilities\Consistency\Delivery\DeliverySemantics;
use Avax\Components\SystemDesign\System\Capabilities\Consistency\Lag\ProjectionLag;
use Avax\Components\SystemDesign\System\Capabilities\Consistency\Lag\ReplicationLag;
use Avax\Components\SystemDesign\System\Capabilities\Consistency\Profiles\ConsistencyModel as ConsistencyModelEnum;
use Avax\Components\SystemDesign\System\Capabilities\Consistency\Profiles\ConsistencyProfile;
use Avax\Components\SystemDesign\System\Capabilities\Consistency\Staleness\StalenessBudget;
use Avax\Components\SystemDesign\System\Flows\DetectConsistencyRisk\DetectConsistencyRisk;
use Avax\Components\SystemDesign\System\Flows\EstimateProjectionLag\EstimateProjectionLag;
use Avax\Components\SystemDesign\System\Flows\EstimateReplicationLag\EstimateReplicationLag;
use Avax\Components\SystemDesign\System\Flows\ExplainConsistencyTradeoff\ExplainConsistencyTradeoff;
use Avax\Components\SystemDesign\System\Flows\ResolveConflict\ResolveConflict;
use Avax\Components\SystemDesign\System\Flows\ValidateConsistencyModel\ValidateConsistencyModel;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * V3-03: Consistency model tests.
 */
final class ConsistencyModelTest extends TestCase
{
    // -- ConsistencyModel enum --

    #[Test]
    public function strongConsistencyGuaranteesReadYourWrites() : void
    {
        self::assertTrue(ConsistencyModelEnum::Strong->guaranteesReadYourWrites());
    }

    #[Test]
    public function sessionConsistencyGuaranteesReadYourWrites() : void
    {
        self::assertTrue(ConsistencyModelEnum::Session->guaranteesReadYourWrites());
    }

    #[Test]
    public function eventualConsistencyDoesNotGuaranteeReadYourWrites() : void
    {
        self::assertFalse(ConsistencyModelEnum::Eventual->guaranteesReadYourWrites());
    }

    #[Test]
    public function strongConsistencyIsGloballyVisible() : void
    {
        self::assertTrue(ConsistencyModelEnum::Strong->isGloballyVisible());
    }

    #[Test]
    public function eventualConsistencyIsNotGloballyVisible() : void
    {
        self::assertFalse(ConsistencyModelEnum::Eventual->isGloballyVisible());
    }

    #[Test]
    public function strongConsistencyHasHighestOverhead() : void
    {
        self::assertSame(20, ConsistencyModelEnum::Strong->estimatedOverheadMs());
    }

    #[Test]
    public function eventualConsistencyHasZeroOverhead() : void
    {
        self::assertSame(0, ConsistencyModelEnum::Eventual->estimatedOverheadMs());
    }

    #[Test]
    public function strongConsistencyHasLowStaleReadRisk() : void
    {
        self::assertSame('low', ConsistencyModelEnum::Strong->staleReadRisk());
    }

    #[Test]
    public function eventualConsistencyHasHighStaleReadRisk() : void
    {
        self::assertSame('high', ConsistencyModelEnum::Eventual->staleReadRisk());
    }

    // -- ConsistencyProfile --

    #[Test]
    public function strongProfileValidatesWithZeroStaleness() : void
    {
        $profile = new ConsistencyProfile('write', ConsistencyModelEnum::Strong, 0);
        $result  = $profile->validate();

        self::assertTrue($result['valid']);
        self::assertSame([], $result['errors']);
    }

    #[Test]
    public function strongProfileRejectsNonZeroStaleness() : void
    {
        $profile = new ConsistencyProfile('write', ConsistencyModelEnum::Strong, 100);
        $result  = $profile->validate();

        self::assertFalse($result['valid']);
        self::assertCount(1, $result['errors']);
    }

    #[Test]
    public function eventualProfileValidatesWithPositiveStaleness() : void
    {
        $profile = new ConsistencyProfile('read', ConsistencyModelEnum::Eventual, 200);
        $result  = $profile->validate();

        self::assertTrue($result['valid']);
    }

    #[Test]
    public function profileReturnsLatencyOverhead() : void
    {
        $profile = new ConsistencyProfile('write', ConsistencyModelEnum::Causal, 50);

        self::assertSame(10, $profile->estimatedLatencyOverheadMs());
    }

    // -- DeliverySemantics --

    #[Test]
    public function atLeastOnceAllowsDuplicates() : void
    {
        self::assertTrue(DeliverySemantics::AtLeastOnce->allowsDuplicates());
    }

    #[Test]
    public function atMostOnceAllowsLoss() : void
    {
        self::assertTrue(DeliverySemantics::AtMostOnce->allowsLoss());
    }

    #[Test]
    public function atLeastOnceDoesNotAllowLoss() : void
    {
        self::assertFalse(DeliverySemantics::AtLeastOnce->allowsLoss());
    }

    #[Test]
    public function deliveryOverheadCosts() : void
    {
        self::assertSame('low', DeliverySemantics::AtMostOnce->overheadCost());
        self::assertSame('medium', DeliverySemantics::AtLeastOnce->overheadCost());
        self::assertSame('high', DeliverySemantics::ExactlyOnceIllusion->overheadCost());
    }

    // -- DeliveryGuarantee --

    #[Test]
    public function validDeliveryGuarantee() : void
    {
        $guarantee = new DeliveryGuarantee(
            path               : 'events',
            semantics          : DeliverySemantics::AtLeastOnce,
            idempotencyRequired: false,
            dedupRequired      : true,
            dedupWindowSeconds : 300,
        );

        self::assertTrue($guarantee->validate()['valid']);
    }

    #[Test]
    public function exactlyOnceRequiresIdempotency() : void
    {
        $guarantee = new DeliveryGuarantee(
            path               : 'payments',
            semantics          : DeliverySemantics::ExactlyOnceIllusion,
            idempotencyRequired: false,
            dedupRequired      : true,
            dedupWindowSeconds : 60,
        );

        $result = $guarantee->validate();

        self::assertFalse($result['valid']);
    }

    #[Test]
    public function atLeastOnceWithoutDedupHasHighDuplicateRisk() : void
    {
        $guarantee = new DeliveryGuarantee(
            path               : 'events',
            semantics          : DeliverySemantics::AtLeastOnce,
            idempotencyRequired: false,
            dedupRequired      : false,
            dedupWindowSeconds : 0,
        );

        self::assertSame('high', $guarantee->duplicateRisk());
    }

    #[Test]
    public function atMostOnceHasLossRisk() : void
    {
        $guarantee = new DeliveryGuarantee(
            path               : 'telemetry',
            semantics          : DeliverySemantics::AtMostOnce,
            idempotencyRequired: false,
            dedupRequired      : false,
            dedupWindowSeconds : 0,
        );

        self::assertSame('medium', $guarantee->lossRisk());
    }

    // -- StalenessBudget --

    #[Test]
    public function stalenessBudgetWithinBudget() : void
    {
        $budget = new StalenessBudget(toleranceMs: 200, path: 'read');

        self::assertTrue($budget->isWithinBudget(150));
        self::assertFalse($budget->isWithinBudget(300));
    }

    #[Test]
    public function stalenessViolationSeverity() : void
    {
        $budget = new StalenessBudget(toleranceMs: 100, path: 'read');

        self::assertSame('none', $budget->violationSeverity(50));
        self::assertSame('low', $budget->violationSeverity(150));
        self::assertSame('medium', $budget->violationSeverity(250));
        self::assertSame('high', $budget->violationSeverity(600));
        self::assertSame('critical', $budget->violationSeverity(1100));
    }

    #[Test]
    public function stalenessHumanReadable() : void
    {
        $budget = new StalenessBudget(toleranceMs: 500, path: 'read');

        self::assertSame('500ms for read', $budget->humanReadable());
    }

    #[Test]
    public function stalenessHumanReadableSeconds() : void
    {
        $budget = new StalenessBudget(toleranceMs: 5000, path: 'analytics');

        self::assertSame('5s for analytics', $budget->humanReadable());
    }

    // -- ConflictStrategy --

    #[Test]
    public function lastWriteWinsCanLoseData() : void
    {
        self::assertTrue(ConflictStrategy::LastWriteWins->canLoseData());
    }

    #[Test]
    public function crdtCannotLoseData() : void
    {
        self::assertFalse(ConflictStrategy::CRDT->canLoseData());
    }

    #[Test]
    public function crdtRequiresApplicationLogic() : void
    {
        self::assertTrue(ConflictStrategy::CRDT->requiresApplicationLogic());
    }

    #[Test]
    public function lastWriteWinsIsLowComplexity() : void
    {
        self::assertSame('low', ConflictStrategy::LastWriteWins->complexityCost());
    }

    #[Test]
    public function versionVectorIsHighComplexity() : void
    {
        self::assertSame('high', ConflictStrategy::VersionVector->complexityCost());
    }

    // -- ConflictResolution --

    #[Test]
    public function validConflictResolution() : void
    {
        $resolution = new ConflictResolution(
            path                 : 'default',
            strategy             : ConflictStrategy::LastWriteWins,
            estimatedConflictRate: 0.01,
            dataLossAccepted     : true,
        );

        self::assertTrue($resolution->validate()['valid']);
    }

    #[Test]
    public function conflictResolutionRejectsDataLossWhenNotAccepted() : void
    {
        $resolution = new ConflictResolution(
            path                 : 'default',
            strategy             : ConflictStrategy::LastWriteWins,
            estimatedConflictRate: 0.05,
            dataLossAccepted     : false,
        );

        $result = $resolution->validate();

        self::assertFalse($result['valid']);
    }

    #[Test]
    public function conflictsPerThousandWrites() : void
    {
        $resolution = new ConflictResolution(
            path                 : 'default',
            strategy             : ConflictStrategy::LastWriteWins,
            estimatedConflictRate: 0.025,
            dataLossAccepted     : true,
        );

        self::assertSame(25.0, $resolution->conflictsPerThousandWrites());
    }

    // -- ProjectionLag --

    #[Test]
    public function validProjectionLag() : void
    {
        $lag = new ProjectionLag(
            expectedMs: 100,
            p95Ms     : 500,
            p99Ms     : 1000,
            projection: 'user_read_model',
        );

        self::assertTrue($lag->validate()['valid']);
    }

    #[Test]
    public function projectionWithinBudget() : void
    {
        $lag = new ProjectionLag(expectedMs: 100, p95Ms: 500, p99Ms: 1000, projection: 'test');

        self::assertTrue($lag->isWithinBudget(1000));
        self::assertTrue($lag->isWithinBudget(500));
        self::assertFalse($lag->isWithinBudget(1500));
    }

    // -- ReplicationLag --

    #[Test]
    public function validReplicationLag() : void
    {
        $lag = new ReplicationLag(
            expectedMs  : 50,
            p95Ms       : 200,
            p99Ms       : 500,
            replicaCount: 2,
            topology    : 'primary-replica',
        );

        self::assertTrue($lag->validate()['valid']);
    }

    #[Test]
    public function worstCaseStaleness() : void
    {
        $lag = new ReplicationLag(expectedMs: 50, p95Ms: 200, p99Ms: 500, replicaCount: 3, topology: 'multi-primary');

        self::assertSame(500, $lag->worstCaseStalenessMs());
    }

    #[Test]
    public function replicationWithinStalenessBudget() : void
    {
        $lag = new ReplicationLag(expectedMs: 50, p95Ms: 200, p99Ms: 500, replicaCount: 2, topology: 'primary-replica');

        self::assertTrue($lag->isWithinStalenessBudget(1000));
        self::assertFalse($lag->isWithinStalenessBudget(200));
    }

    // -- ConsistencyModel aggregate --

    #[Test]
    public function consistencyModelFromMinimalConfig() : void
    {
        $config = [
            'system'      => 'test-app',
            'consistency' => [
                'write_path' => 'strong',
                'read_path'  => 'read_your_writes',
                'analytics'  => 'eventual',
            ],
        ];

        $model  = ConsistencyModel::fromConfig($config);
        $result = $model->validate();

        self::assertTrue($result['valid']);
        self::assertSame('test-app', $model->system);
        self::assertCount(3, $model->profiles);
        self::assertCount(3, $model->stalenessBudgets);
    }

    #[Test]
    public function consistencyModelWithDelivery() : void
    {
        $config = [
            'system'      => 'test-app',
            'consistency' => [
                'write_path' => 'strong',
                'read_path'  => 'eventual',
                'analytics'  => 'eventual',
                'delivery'   => [
                    'semantics'            => 'at_least_once',
                    'idempotency_required' => true,
                    'dedup_required'       => true,
                    'dedup_window_seconds' => 600,
                ],
            ],
        ];

        $model = ConsistencyModel::fromConfig($config);

        self::assertCount(1, $model->deliveryGuarantees);
        self::assertSame('at_least_once', $model->deliveryGuarantees[0]->semantics->value);
    }

    #[Test]
    public function consistencyModelWithConflicts() : void
    {
        $config = [
            'system'      => 'test-app',
            'consistency' => [
                'write_path' => 'eventual',
                'read_path'  => 'eventual',
                'analytics'  => 'eventual',
                'conflicts'  => [
                    'strategy'           => 'last_write_wins',
                    'estimated_rate'     => 0.02,
                    'data_loss_accepted' => true,
                ],
            ],
        ];

        $model = ConsistencyModel::fromConfig($config);

        self::assertCount(1, $model->conflictResolutions);
        self::assertSame('last_write_wins', $model->conflictResolutions[0]->strategy->value);
    }

    #[Test]
    public function consistencyModelWithProjections() : void
    {
        $config = [
            'system'      => 'test-app',
            'consistency' => [
                'write_path'  => 'strong',
                'read_path'   => 'strong',
                'analytics'   => 'eventual',
                'projections' => [
                    'user_view'  => [
                        'expected_ms' => 50,
                        'p95_ms'      => 200,
                        'p99_ms'      => 500,
                    ],
                    'order_view' => [
                        'expected_ms' => 100,
                        'p95_ms'      => 400,
                        'p99_ms'      => 800,
                    ],
                ],
            ],
        ];

        $model = ConsistencyModel::fromConfig($config);

        self::assertCount(2, $model->projectionLags);
        self::assertSame('user_view', $model->projectionLags[0]->projection);
    }

    #[Test]
    public function consistencyModelWithReplication() : void
    {
        $config = [
            'system'      => 'test-app',
            'consistency' => [
                'write_path'  => 'strong',
                'read_path'   => 'eventual',
                'analytics'   => 'eventual',
                'replication' => [
                    'expected_ms'   => 30,
                    'p95_ms'        => 150,
                    'p99_ms'        => 400,
                    'replica_count' => 3,
                    'topology'      => 'primary-replica',
                ],
            ],
        ];

        $model = ConsistencyModel::fromConfig($config);

        self::assertNotNull($model->replicationLag);
        self::assertSame(3, $model->replicationLag->replicaCount);
    }

    #[Test]
    public function totalLatencyOverhead() : void
    {
        $config = [
            'system'      => 'test-app',
            'consistency' => [
                'write_path' => 'strong',
                'read_path'  => 'eventual',
                'analytics'  => 'eventual',
            ],
        ];

        $model = ConsistencyModel::fromConfig($config);

        // strong=20, eventual=0, eventual=0
        self::assertSame(20, $model->totalEstimatedLatencyOverheadMs());
    }

    #[Test]
    public function highStaleReadRiskPaths() : void
    {
        $config = [
            'system'      => 'test-app',
            'consistency' => [
                'write_path' => 'strong',
                'read_path'  => 'eventual',
                'analytics'  => 'eventual',
            ],
        ];

        $model      = ConsistencyModel::fromConfig($config);
        $riskyPaths = $model->highStaleReadRiskPaths();

        self::assertContains('read_path', $riskyPaths);
        self::assertContains('analytics', $riskyPaths);
        self::assertNotContains('write_path', $riskyPaths);
    }

    // -- ValidateConsistencyModel flow --

    #[Test]
    public function validateConsistencyModelFlowPasses() : void
    {
        $config = [
            'system'      => 'test-app',
            'consistency' => [
                'write_path' => 'strong',
                'read_path'  => 'session',
                'analytics'  => 'eventual',
            ],
        ];

        $result = (new ValidateConsistencyModel())->execute($config);

        self::assertTrue($result['valid']);
        self::assertNotNull($result['model']);
    }

    #[Test]
    public function validateConsistencyModelFlowFailsOnInvalidConfig() : void
    {
        $config = [
            'system'      => 'test-app',
            'consistency' => [
                'write_path' => 'strong',
                'read_path'  => 'eventual',
                'analytics'  => 'eventual',
                'conflicts'  => [
                    'strategy'           => 'last_write_wins',
                    'estimated_rate'     => 0.05,
                    'data_loss_accepted' => false,
                ],
            ],
        ];

        $result = (new ValidateConsistencyModel())->execute($config);

        self::assertFalse($result['valid']);
    }

    // -- ExplainConsistencyTradeoff flow --

    #[Test]
    public function explainConsistencyTradeoff() : void
    {
        $config = [
            'system'      => 'url-shortener',
            'consistency' => [
                'write_path' => 'strong',
                'read_path'  => 'eventual',
                'analytics'  => 'eventual',
            ],
        ];

        $model  = ConsistencyModel::fromConfig($config);
        $result = (new ExplainConsistencyTradeoff())->execute($model);

        self::assertSame('url-shortener', $result['system']);
        self::assertGreaterThan(0, $result['total_latency_overhead_ms']);
        self::assertCount(3, $result['paths']);
    }

    // -- DetectConsistencyRisk flow --

    #[Test]
    public function detectConsistencyRiskFindsReadWriteMismatch() : void
    {
        $config = [
            'system'      => 'test-app',
            'consistency' => [
                'write_path' => 'strong',
                'read_path'  => 'eventual',
                'analytics'  => 'eventual',
            ],
        ];

        $model = ConsistencyModel::fromConfig($config);
        $risks = (new DetectConsistencyRisk())->execute($model);

        $mismatchRisk = array_filter($risks, fn ($r) => $r['category'] === 'read_write_mismatch');

        self::assertNotEmpty($mismatchRisk);
    }

    #[Test]
    public function detectConsistencyRiskFindsReplicationLagExceedsBudget() : void
    {
        $config = [
            'system'      => 'test-app',
            'consistency' => [
                'write_path'  => 'strong',
                'read_path'   => 'eventual',
                'analytics'   => 'eventual',
                'replication' => [
                    'expected_ms'   => 100,
                    'p95_ms'        => 500,
                    'p99_ms'        => 2000,
                    'replica_count' => 3,
                    'topology'      => 'primary-replica',
                ],
            ],
        ];

        $model = ConsistencyModel::fromConfig($config);
        $risks = (new DetectConsistencyRisk())->execute($model);

        $lagRisk = array_filter($risks, fn ($r) => $r['category'] === 'replication_lag_exceeds_budget');

        self::assertNotEmpty($lagRisk);
    }

    #[Test]
    public function detectConsistencyRiskNoRisksOnCompatibleConfig() : void
    {
        $config = [
            'system'      => 'test-app',
            'consistency' => [
                'write_path' => 'strong',
                'read_path'  => 'strong',
                'analytics'  => 'eventual',
            ],
        ];

        $model = ConsistencyModel::fromConfig($config);
        $risks = (new DetectConsistencyRisk())->execute($model);

        self::assertEmpty($risks);
    }

    // -- EstimateProjectionLag flow --

    #[Test]
    public function estimateProjectionLag() : void
    {
        $config = [
            'system'      => 'test-app',
            'consistency' => [
                'write_path'  => 'strong',
                'read_path'   => 'strong',
                'analytics'   => 'eventual',
                'projections' => [
                    'user_view'  => ['expected_ms' => 50, 'p95_ms' => 200, 'p99_ms' => 500],
                    'order_view' => ['expected_ms' => 100, 'p95_ms' => 400, 'p99_ms' => 1200],
                ],
            ],
        ];

        $model  = ConsistencyModel::fromConfig($config);
        $result = (new EstimateProjectionLag())->execute($model, 1000);

        self::assertSame(2, $result['projection_count']);
        self::assertSame(1200, $result['max_p99_ms']);
        // user_view p99=500 <= 1000 threshold, order_view p99=1200 > 1000
        self::assertTrue($result['projections'][0]['within_budget']);
        self::assertFalse($result['projections'][1]['within_budget']);
    }

    #[Test]
    public function estimateProjectionLagNoProjections() : void
    {
        $config = [
            'system'      => 'test-app',
            'consistency' => [
                'write_path' => 'strong',
                'read_path'  => 'strong',
                'analytics'  => 'eventual',
            ],
        ];

        $model  = ConsistencyModel::fromConfig($config);
        $result = (new EstimateProjectionLag())->execute($model);

        self::assertSame(0, $result['projection_count']);
    }

    // -- EstimateReplicationLag flow --

    #[Test]
    public function estimateReplicationLagWithReplication() : void
    {
        $config = [
            'system'      => 'test-app',
            'consistency' => [
                'write_path'         => 'eventual',
                'read_path'          => 'eventual',
                'analytics'          => 'eventual',
                'write_staleness_ms' => 500,
                'read_staleness_ms'  => 500,
                'replication'        => [
                    'expected_ms'   => 50,
                    'p95_ms'        => 200,
                    'p99_ms'        => 500,
                    'replica_count' => 2,
                    'topology'      => 'primary-replica',
                ],
            ],
        ];

        $model  = ConsistencyModel::fromConfig($config);
        $result = (new EstimateReplicationLag())->execute($model);

        self::assertTrue($result['has_replication']);
        self::assertSame(500, $result['worst_case_staleness_ms']);
        self::assertEmpty($result['budgets_exceeded']);
    }

    #[Test]
    public function estimateReplicationLagWithoutReplication() : void
    {
        $config = [
            'system'      => 'test-app',
            'consistency' => [
                'write_path' => 'strong',
                'read_path'  => 'strong',
                'analytics'  => 'eventual',
            ],
        ];

        $model  = ConsistencyModel::fromConfig($config);
        $result = (new EstimateReplicationLag())->execute($model);

        self::assertFalse($result['has_replication']);
    }

    #[Test]
    public function estimateReplicationLagBudgetsExceeded() : void
    {
        $config = [
            'system'      => 'test-app',
            'consistency' => [
                'write_path'         => 'strong',
                'read_path'          => 'eventual',
                'analytics'          => 'eventual',
                'write_staleness_ms' => 100,
                'replication'        => [
                    'expected_ms'   => 50,
                    'p95_ms'        => 300,
                    'p99_ms'        => 600,
                    'replica_count' => 3,
                    'topology'      => 'primary-replica',
                ],
            ],
        ];

        $model  = ConsistencyModel::fromConfig($config);
        $result = (new EstimateReplicationLag())->execute($model);

        self::assertNotEmpty($result['budgets_exceeded']);
    }

    // -- ResolveConflict flow --

    #[Test]
    public function resolveConflictAnalysis() : void
    {
        $config = [
            'system'      => 'test-app',
            'consistency' => [
                'write_path' => 'eventual',
                'read_path'  => 'eventual',
                'analytics'  => 'eventual',
                'conflicts'  => [
                    'strategy'           => 'last_write_wins',
                    'estimated_rate'     => 0.03,
                    'data_loss_accepted' => true,
                ],
            ],
        ];

        $model  = ConsistencyModel::fromConfig($config);
        $result = (new ResolveConflict())->execute($model);

        self::assertCount(1, $result['conflicts']);
        self::assertSame(30.0, $result['conflicts'][0]['conflicts_per_1000_writes']);
        self::assertTrue($result['conflicts'][0]['data_loss_risk']);
    }
}
