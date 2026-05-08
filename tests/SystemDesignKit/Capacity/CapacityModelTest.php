<?php

declare(strict_types=1);

namespace Avax\Tests\SystemDesignKit\Capacity;

use Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\Availability\FailureBudget;
use Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\Availability\Slo;
use Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\Cache\CacheHitRatio;
use Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\Cache\CacheStampedeRisk;
use Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\CapacityModel;
use Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\Latency\LatencyBudget;
use Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\Queue\ConsumerThroughput;
use Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\Queue\QueueDepth;
use Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\Storage\StorageGrowth;
use Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\Traffic\FanoutSize;
use Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\Traffic\PeakTrafficMultiplier;
use Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\Traffic\RequestsPerSecond;
use Avax\Labs\SystemDesignKit\System\Flows\EstimateCacheEffectiveness\EstimateCacheEffectiveness;
use Avax\Labs\SystemDesignKit\System\Flows\EstimateFailureBudget\EstimateFailureBudget;
use Avax\Labs\SystemDesignKit\System\Flows\EstimateLatencyBudget\EstimateLatencyBudget;
use Avax\Labs\SystemDesignKit\System\Flows\EstimateQueuePressure\EstimateQueuePressure;
use Avax\Labs\SystemDesignKit\System\Flows\EstimateStorageGrowth\EstimateStorageGrowth;
use Avax\Labs\SystemDesignKit\System\Flows\EstimateTrafficLoad\EstimateTrafficLoad;
use Avax\Labs\SystemDesignKit\System\Flows\ValidateCapacityModel\ValidateCapacityModel;
use Avax\Labs\SystemDesignKit\System\PublicSurface\SystemDesignKit;
use Avax\Tests\TestCase;

final class CapacityModelTest extends TestCase
{
    public function testRequestsPerSecondCalculatesRatio() : void
    {
        $rps = new RequestsPerSecond(total: 100, reads: 90, writes: 10);

        self::assertSame(9.0, $rps->readWriteRatio());
    }

    // --- Value object tests ---

    public function testRequestsPerSecondValidatesSum() : void
    {
        $rps    = new RequestsPerSecond(total: 100, reads: 90, writes: 10);
        $result = $rps->validate();

        self::assertTrue($result['valid']);
        self::assertSame([], $result['errors']);
    }

    public function testRequestsPerSecondRejectsMismatchedSum() : void
    {
        $rps    = new RequestsPerSecond(total: 200, reads: 90, writes: 10);
        $result = $rps->validate();

        self::assertFalse($result['valid']);
        self::assertNotEmpty($result['errors']);
    }

    public function testStorageGrowthCalculatesDaily() : void
    {
        $growth = new StorageGrowth(growthPerDay: 1000, averageRecordSizeBytes: 512, retentionDays: 365);

        self::assertSame(512000, $growth->dailyGrowthBytes());
    }

    public function testStorageGrowthCalculatesTotal() : void
    {
        $growth = new StorageGrowth(growthPerDay: 1000, averageRecordSizeBytes: 512, retentionDays: 365);

        self::assertSame(512000 * 365, $growth->totalRetentionBytes());
    }

    public function testStorageGrowthHumanReadable() : void
    {
        $growth = new StorageGrowth(growthPerDay: 1000000, averageRecordSizeBytes: 512, retentionDays: 3650);
        $human  = $growth->totalRetentionHumanReadable();

        self::assertTrue(
            str_contains($human, 'GB') || str_contains($human, 'TB'),
            "Expected GB or TB in '{$human}'",
        );
    }

    public function testCacheHitRatioCalculatesMisses() : void
    {
        $ratio = new CacheHitRatio(target: 0.95);

        self::assertEqualsWithDelta(0.05, $ratio->missRatio(), 0.0001);
        self::assertEqualsWithDelta(95000.0, $ratio->cacheHitsPerSecond(100000), 0.01);
        self::assertEqualsWithDelta(5000.0, $ratio->cacheMissesPerSecond(100000), 0.01);
    }

    public function testLatencyBudgetValidatesOrdering() : void
    {
        $budget = new LatencyBudget(p50Ms: 10, p95Ms: 50, p99Ms: 100);
        $result = $budget->validate();

        self::assertTrue($result['valid']);
    }

    public function testLatencyBudgetRejectsWrongOrder() : void
    {
        $budget = new LatencyBudget(p50Ms: 100, p95Ms: 50, p99Ms: 10);
        $result = $budget->validate();

        self::assertFalse($result['valid']);
        self::assertCount(2, $result['errors']);
    }

    public function testLatencyBudgetChecksMeasured() : void
    {
        $budget = new LatencyBudget(p50Ms: 10, p95Ms: 50, p99Ms: 100);
        $result = $budget->isWithinBudget(measuredP50: 8, measuredP95: 45, measuredP99: 90);

        self::assertTrue($result['within_budget']);
    }

    public function testLatencyBudgetDetectsViolation() : void
    {
        $budget = new LatencyBudget(p50Ms: 10, p95Ms: 50, p99Ms: 100);
        $result = $budget->isWithinBudget(measuredP50: 15, measuredP95: 45, measuredP99: 90);

        self::assertFalse($result['within_budget']);
        self::assertCount(1, $result['violations']);
    }

    public function testSloCalculatesDowntime() : void
    {
        $slo = new Slo(percentage: 99.9);

        self::assertGreaterThan(0, $slo->monthlyDowntimeMinutes());
        self::assertStringContainsString('minutes', $slo->monthlyDowntimeHumanReadable());
    }

    public function testSloHighAvailability() : void
    {
        $slo = new Slo(percentage: 99.99);

        self::assertStringContainsString('minutes', $slo->monthlyDowntimeHumanReadable());
    }

    public function testFailureBudgetAbsorbsIncident() : void
    {
        $budget = new FailureBudget(minutesPerMonth: 4.38);

        self::assertTrue($budget->canAbsorbIncident(1.0, 0.0));
        self::assertFalse($budget->canAbsorbIncident(5.0, 0.0));
    }

    public function testFailureBudgetRemaining() : void
    {
        $budget = new FailureBudget(minutesPerMonth: 10.0);

        self::assertSame(7.0, $budget->remainingMinutes(3.0));
        self::assertSame(0.0, $budget->remainingMinutes(15.0));
    }

    public function testQueueDepthUtilization() : void
    {
        $depth = new QueueDepth(maxDepth: 1000, currentDepth: 800);

        self::assertSame(0.8, $depth->utilizationRatio());
        self::assertTrue($depth->isNearCapacity(0.8));
    }

    public function testConsumerThroughputRequired() : void
    {
        $throughput = new ConsumerThroughput(perSecond: 1000);

        self::assertSame(10, $throughput->requiredConsumers(10000));
    }

    public function testPeakTrafficMultiplier() : void
    {
        $multiplier = new PeakTrafficMultiplier(multiplier: 3);

        self::assertSame(300000, $multiplier->applyTo(100000));
    }

    public function testCapacityModelValidatesAllComponents() : void
    {
        $model  = $this->makeValidModel();
        $result = $model->validate();

        self::assertTrue($result['valid']);
        self::assertSame([], $result['errors']);
    }

    // --- CapacityModel tests ---

    private function makeValidModel() : CapacityModel
    {
        return new CapacityModel(
            system            : 'url-shortener',
            traffic           : new RequestsPerSecond(total: 100000, reads: 99000, writes: 1000),
            peakMultiplier    : new PeakTrafficMultiplier(multiplier: 3),
            storage           : new StorageGrowth(growthPerDay: 1000000, averageRecordSizeBytes: 512, retentionDays: 3650),
            cacheHitRatio     : new CacheHitRatio(target: 0.98),
            cacheStampede     : new CacheStampedeRisk(protectionRequired: true),
            queueDepth        : new QueueDepth(maxDepth: 100000),
            consumerThroughput: new ConsumerThroughput(perSecond: 5000),
            latencyBudget     : new LatencyBudget(p50Ms: 10, p95Ms: 50, p99Ms: 100),
            slo               : new Slo(percentage: 99.99),
            failureBudget     : new FailureBudget(minutesPerMonth: 4.38),
        );
    }

    public function testCapacityModelFromConfig() : void
    {
        $config = [
            'system'       => 'test',
            'traffic'      => [
                'requests_per_second' => 1000,
                'reads_per_second'    => 900,
                'writes_per_second'   => 100,
                'peak_multiplier'     => 2,
            ],
            'storage'      => [
                'growth_per_day'            => 100,
                'average_record_size_bytes' => 256,
                'retention_days'            => 365,
            ],
            'cache'        => [
                'hit_ratio_target'             => 0.9,
                'stampede_protection_required' => true,
            ],
            'queue'        => [
                'max_depth'                      => 10000,
                'consumer_throughput_per_second' => 500,
            ],
            'latency'      => [
                'p50_ms' => 20,
                'p95_ms' => 100,
                'p99_ms' => 200,
            ],
            'availability' => [
                'slo'                              => 99.9,
                'failure_budget_minutes_per_month' => 43.8,
            ],
        ];

        $model  = CapacityModel::fromConfig($config);
        $result = $model->validate();

        self::assertTrue($result['valid']);
        self::assertSame('test', $model->system);
    }

    public function testCapacityModelPeakRps() : void
    {
        $model = $this->makeValidModel();

        self::assertSame(300000, $model->estimatedPeakRps());
    }

    public function testCapacityModelCanHandleWriteLoad() : void
    {
        $model = $this->makeValidModel();

        self::assertTrue($model->canHandleWriteLoad());
    }

    // --- Flow tests ---

    public function testEstimateTrafficLoad() : void
    {
        $model  = $this->makeValidModel();
        $flow   = new EstimateTrafficLoad();
        $result = $flow->execute($model);

        self::assertSame(300000, $result['peak_rps']);
        self::assertSame(99000, $result['read_rps']);
        self::assertSame(1000, $result['write_rps']);
        self::assertGreaterThan(0, $result['cache_hits_per_second']);
    }

    public function testEstimateStorageGrowth() : void
    {
        $model  = $this->makeValidModel();
        $flow   = new EstimateStorageGrowth();
        $result = $flow->execute($model);

        self::assertGreaterThan(0, $result['daily_growth_bytes']);
        self::assertTrue(
            str_contains($result['retention_human'], 'GB') || str_contains($result['retention_human'], 'TB'),
            "Expected GB or TB in '{$result['retention_human']}'",
        );
    }

    public function testEstimateCacheEffectiveness() : void
    {
        $model  = $this->makeValidModel();
        $flow   = new EstimateCacheEffectiveness();
        $result = $flow->execute($model);

        self::assertSame(0.98, $result['hit_ratio']);
        self::assertSame('98%', $result['cache_savings_ratio']);
    }

    public function testEstimateQueuePressure() : void
    {
        $model  = $this->makeValidModel();
        $flow   = new EstimateQueuePressure();
        $result = $flow->execute($model);

        self::assertTrue($result['can_handle_load']);
        self::assertFalse($result['consumers_underprovisioned']);
    }

    public function testEstimateLatencyBudget() : void
    {
        $model  = $this->makeValidModel();
        $flow   = new EstimateLatencyBudget();
        $result = $flow->execute($model);

        self::assertTrue($result['budget_valid']);
        self::assertArrayHasKey('database', $result['allocation']);
        self::assertSame(10, $result['p50_budget_ms']);
    }

    public function testEstimateFailureBudget() : void
    {
        $model  = $this->makeValidModel();
        $flow   = new EstimateFailureBudget();
        $result = $flow->execute($model);

        self::assertSame(99.99, $result['slo_percentage']);
        self::assertSame(4.38, $result['failure_budget_minutes']);
        self::assertStringContainsString('minutes', $result['monthly_downtime_human']);
    }

    // --- Public surface tests ---

    public function testSystemDesignKitValidateCapacityFile() : void
    {
        $result = SystemDesignKit::validateCapacity(
            'labs/SystemDesignKit/examples/valid-capacity.yaml',
        );

        self::assertTrue($result['valid']);
        self::assertSame([], $result['schema_errors']);
        self::assertNotNull($result['model']);
    }

    public function testSystemDesignKitBuildCapacityModel() : void
    {
        $config = [
            'system'       => 'test',
            'traffic'      => ['requests_per_second' => 100, 'reads_per_second' => 90, 'writes_per_second' => 10, 'peak_multiplier' => 1],
            'storage'      => ['growth_per_day' => 10, 'average_record_size_bytes' => 100, 'retention_days' => 30],
            'cache'        => ['hit_ratio_target' => 0.9, 'stampede_protection_required' => false],
            'queue'        => ['max_depth' => 1000, 'consumer_throughput_per_second' => 100],
            'latency'      => ['p50_ms' => 10, 'p95_ms' => 50, 'p99_ms' => 100],
            'availability' => ['slo' => 99.9, 'failure_budget_minutes_per_month' => 43.8],
        ];

        $model = SystemDesignKit::buildCapacityModel($config);

        self::assertInstanceOf(CapacityModel::class, $model);
        self::assertSame('test', $model->system);
    }

    public function testSystemDesignKitEstimateMethods() : void
    {
        $config = [
            'system'       => 'test',
            'traffic'      => ['requests_per_second' => 10000, 'reads_per_second' => 9000, 'writes_per_second' => 1000, 'peak_multiplier' => 2],
            'storage'      => ['growth_per_day' => 1000, 'average_record_size_bytes' => 512, 'retention_days' => 365],
            'cache'        => ['hit_ratio_target' => 0.95, 'stampede_protection_required' => true],
            'queue'        => ['max_depth' => 50000, 'consumer_throughput_per_second' => 2000],
            'latency'      => ['p50_ms' => 20, 'p95_ms' => 100, 'p99_ms' => 200],
            'availability' => ['slo' => 99.9, 'failure_budget_minutes_per_month' => 43.8],
        ];

        $model = SystemDesignKit::buildCapacityModel($config);

        $traffic = SystemDesignKit::estimateTrafficLoad($model);
        self::assertSame(20000, $traffic['peak_rps']);

        $storage = SystemDesignKit::estimateStorageGrowth($model);
        self::assertGreaterThan(0, $storage['daily_growth_bytes']);

        $cache = SystemDesignKit::estimateCacheEffectiveness($model);
        self::assertSame(0.95, $cache['hit_ratio']);

        $queue = SystemDesignKit::estimateQueuePressure($model);
        self::assertTrue($queue['can_handle_load']);

        $latency = SystemDesignKit::estimateLatencyBudget($model);
        self::assertTrue($latency['budget_valid']);

        $failure = SystemDesignKit::estimateFailureBudget($model);
        self::assertSame(99.9, $failure['slo_percentage']);
    }

    // --- ValidateCapacityModel flow test ---

    public function testValidateCapacityModelFlowWithValidFile() : void
    {
        $flow   = new ValidateCapacityModel();
        $result = $flow->execute('labs/SystemDesignKit/examples/valid-capacity.yaml');

        self::assertTrue($result['valid']);
        self::assertSame([], $result['schema_errors']);
        self::assertNotNull($result['model']);
    }

    public function testValidateCapacityModelFlowWithInvalidFile() : void
    {
        $flow   = new ValidateCapacityModel();
        $result = $flow->execute('labs/SystemDesignKit/examples/invalid-capacity-bad-values.yaml');

        self::assertFalse($result['valid']);
        // Either schema errors or model errors (or both)
        self::assertTrue(
            ($result['schema_errors'] !== []) || ! empty($result['model_errors'] ?? []),
        );
    }
}
