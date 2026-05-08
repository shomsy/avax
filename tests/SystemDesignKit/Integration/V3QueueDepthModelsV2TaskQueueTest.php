<?php

declare(strict_types=1);

namespace Avax\Tests\SystemDesignKit\Integration;

use Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\Availability\FailureBudget;
use Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\Availability\Slo;
use Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\Cache\CacheHitRatio;
use Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\Cache\CacheStampedeRisk;
use Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\CapacityModel;
use Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\Latency\LatencyBudget;
use Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\Queue\ConsumerThroughput;
use Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\Queue\QueueDepth;
use Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\Storage\StorageGrowth;
use Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\Traffic\PeakTrafficMultiplier;
use Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\Traffic\RequestsPerSecond;
use Avax\Labs\SystemDesignKit\System\Flows\EstimateQueuePressure\EstimateQueuePressure;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * V3-05: Prove V3 QueueDepth models V2 TaskQueue capacity characteristics.
 *
 * V3 = design-time capacity modeling. V2 TaskQueue = runtime priority queue.
 * V3 models capacity limits; V2 executes queue operations.
 */
final class V3QueueDepthModelsV2TaskQueueTest extends TestCase
{
    #[Test]
    public function v3QueueDepthModelsV2TaskQueueUtilization() : void
    {
        // V2 TaskQueue has push/pop/size operations with no explicit max depth.
        // V3 models the desired max depth and current utilization.
        $depth = new QueueDepth(maxDepth: 10000, currentDepth: 7500);

        self::assertSame(0.75, $depth->utilizationRatio());
        self::assertTrue($depth->isNearCapacity(0.7));
        self::assertFalse($depth->isNearCapacity(0.9));
    }

    #[Test]
    public function v3ConsumerThroughputModelsV2TaskRunnerCapacity() : void
    {
        // V2 TaskRunner executes tasks sequentially with history tracking.
        // V3 models required consumer throughput for capacity planning.
        $throughput = new ConsumerThroughput(perSecond: 500, consumerCount: 4);

        // requiredConsumers(1000) = ceil(1000 / 500) = 2 consumers needed
        self::assertSame(2, $throughput->requiredConsumers(1000));
        self::assertSame(500, $throughput->perSecond);
        // totalThroughput = perSecond * consumerCount = 500 * 4 = 2000
        self::assertSame(2000, $throughput->totalThroughput());
    }

    #[Test]
    public function v3QueuePressureEstimationForV2TaskSystem() : void
    {
        // Build a capacity model for a system using V2 Tasks.
        // V3 estimates whether the queue can handle the load.
        $model = new CapacityModel(
            system            : 'v2-task-system',
            traffic           : new RequestsPerSecond(total: 5000, reads: 4000, writes: 1000),
            peakMultiplier    : new PeakTrafficMultiplier(multiplier: 2),
            storage           : new StorageGrowth(growthPerDay: 100000, averageRecordSizeBytes: 256, retentionDays: 90),
            cacheHitRatio     : new CacheHitRatio(target: 0.9),
            cacheStampede     : new CacheStampedeRisk(protectionRequired: true),
            queueDepth        : new QueueDepth(maxDepth: 50000),
            consumerThroughput: new ConsumerThroughput(perSecond: 5000),
            latencyBudget     : new LatencyBudget(p50Ms: 20, p95Ms: 100, p99Ms: 200),
            slo               : new Slo(percentage: 99.9),
            failureBudget     : new FailureBudget(minutesPerMonth: 43.8),
        );

        $result = (new EstimateQueuePressure())->execute($model);

        // V3 estimates whether queue capacity can handle projected load
        self::assertArrayHasKey('can_handle_load', $result);
        self::assertArrayHasKey('queue_utilization', $result);
        self::assertArrayHasKey('consumers_underprovisioned', $result);
    }

    #[Test]
    public function v3QueueDepthDetectsOverprovisionedConsumers() : void
    {
        // Model where consumer throughput is insufficient for queue depth
        $model = new CapacityModel(
            system            : 'overloaded-queue',
            traffic           : new RequestsPerSecond(total: 10000, reads: 9000, writes: 1000),
            peakMultiplier    : new PeakTrafficMultiplier(multiplier: 3),
            storage           : new StorageGrowth(growthPerDay: 500, averageRecordSizeBytes: 128, retentionDays: 30),
            cacheHitRatio     : new CacheHitRatio(target: 0.8),
            cacheStampede     : new CacheStampedeRisk(protectionRequired: false),
            queueDepth        : new QueueDepth(maxDepth: 1000, currentDepth: 900),
            consumerThroughput: new ConsumerThroughput(perSecond: 100),
            latencyBudget     : new LatencyBudget(p50Ms: 50, p95Ms: 200, p99Ms: 500),
            slo               : new Slo(percentage: 99.0),
            failureBudget     : new FailureBudget(minutesPerMonth: 438),
        );

        $result = (new EstimateQueuePressure())->execute($model);

        self::assertFalse($result['can_handle_load']);
        self::assertTrue($result['consumers_underprovisioned']);
    }

    #[Test]
    public function v3QueueDepthValidatesV2TaskQueueDesign() : void
    {
        // V3 validates that a V2 TaskQueue-based design has sufficient capacity
        $depth = new QueueDepth(maxDepth: 1000, currentDepth: 500);

        $result = $depth->validate();
        self::assertTrue($result['valid']);
        self::assertSame([], $result['errors']);
    }

    #[Test]
    public function v3QueueDepthRejectsCurrentExceedingMax() : void
    {
        $depth = new QueueDepth(maxDepth: 100, currentDepth: 200);

        $result = $depth->validate();
        self::assertFalse($result['valid']);
        self::assertNotEmpty($result['errors']);
    }
}
