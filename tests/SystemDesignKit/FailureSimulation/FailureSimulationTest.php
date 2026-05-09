<?php

declare(strict_types=1);

namespace Avax\Tests\SystemDesignKit\FailureSimulation;

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
use Avax\Labs\SystemDesignKit\System\Capabilities\FailureSimulation\FailureSimulation;
use Avax\Labs\SystemDesignKit\System\Flows\RunFailureSimulations\RunFailureSimulations;
use PHPUnit\Framework\TestCase;

final class FailureSimulationTest extends TestCase
{
    private CapacityModel $model;

    public function test_failure_simulation_creation() : void
    {
        $sim = new FailureSimulation(
            name       : 'cache-outage',
            failureMode: 'cache_outage',
            description: 'Cache outage simulation',
            parameters : [],
        );

        self::assertSame('cache-outage', $sim->name);
        self::assertSame('cache_outage', $sim->failureMode);
    }

    public function test_failure_simulation_from_config() : void
    {
        $config = [
            'name'         => 'test-failure',
            'failure_mode' => 'queue_flood',
            'description'  => 'Queue flood test',
            'parameters'   => ['threshold' => 100],
        ];

        $sim = FailureSimulation::fromConfig($config);

        self::assertSame('test-failure', $sim->name);
        self::assertSame('queue_flood', $sim->failureMode);
    }

    public function test_cache_outage_with_protection_detects_no_violation() : void
    {
        $sim    = FailureSimulation::fromConfig([
                                                    'name'         => 'cache-outage',
                                                    'failure_mode' => 'cache_outage',
                                                    'description'  => 'Cache outage with stampede protection',
                                                    'parameters'   => [],
                                                ]);
        $result = $sim->run($this->model);

        self::assertFalse($result['violation_detected']);
    }

    public function test_cache_outage_without_protection_detects_violation() : void
    {
        $modelWithoutProtection = new CapacityModel(
            system            : 'test-system',
            traffic           : new RequestsPerSecond(total: 10000, reads: 9000, writes: 1000),
            peakMultiplier    : new PeakTrafficMultiplier(multiplier: 3),
            storage           : new StorageGrowth(growthPerDay: 1000000, averageRecordSizeBytes: 512, retentionDays: 365),
            cacheHitRatio     : new CacheHitRatio(target: 0.95),
            cacheStampede     : new CacheStampedeRisk(protectionRequired: false),
            queueDepth        : new QueueDepth(maxDepth: 100000),
            consumerThroughput: new ConsumerThroughput(perSecond: 2000),
            latencyBudget     : new LatencyBudget(p50Ms: 50, p95Ms: 200, p99Ms: 500),
            slo               : new Slo(percentage: 99.9),
            failureBudget     : new FailureBudget(minutesPerMonth: 43.8),
        );

        $sim    = FailureSimulation::fromConfig([
                                                    'name'         => 'cache-outage',
                                                    'failure_mode' => 'cache_outage',
                                                    'description'  => 'Cache outage without protection',
                                                    'parameters'   => [],
                                                ]);
        $result = $sim->run($modelWithoutProtection);

        self::assertTrue($result['violation_detected']);
        self::assertSame('critical', $result['violation']['severity']);
    }

    public function test_queue_flood_detects_violation_when_underprovisioned() : void
    {
        $underprovisionedModel = new CapacityModel(
            system            : 'test-system',
            traffic           : new RequestsPerSecond(total: 10000, reads: 1000, writes: 9000),
            peakMultiplier    : new PeakTrafficMultiplier(multiplier: 3),
            storage           : new StorageGrowth(growthPerDay: 1000000, averageRecordSizeBytes: 512, retentionDays: 365),
            cacheHitRatio     : new CacheHitRatio(target: 0.95),
            cacheStampede     : new CacheStampedeRisk(protectionRequired: true),
            queueDepth        : new QueueDepth(maxDepth: 100000),
            consumerThroughput: new ConsumerThroughput(perSecond: 100),
            latencyBudget     : new LatencyBudget(p50Ms: 50, p95Ms: 200, p99Ms: 500),
            slo               : new Slo(percentage: 99.9),
            failureBudget     : new FailureBudget(minutesPerMonth: 43.8),
        );

        $sim    = FailureSimulation::fromConfig([
                                                    'name'         => 'queue-flood',
                                                    'failure_mode' => 'queue_flood',
                                                    'description'  => 'Queue flood simulation',
                                                    'parameters'   => [],
                                                ]);
        $result = $sim->run($underprovisionedModel);

        self::assertTrue($result['violation_detected']);
        self::assertSame('critical', $result['violation']['severity']);
    }

    public function test_database_slow_detects_violation_on_high_latency() : void
    {
        $slowModel = new CapacityModel(
            system            : 'test-system',
            traffic           : new RequestsPerSecond(total: 10000, reads: 9000, writes: 1000),
            peakMultiplier    : new PeakTrafficMultiplier(multiplier: 3),
            storage           : new StorageGrowth(growthPerDay: 1000000, averageRecordSizeBytes: 512, retentionDays: 365),
            cacheHitRatio     : new CacheHitRatio(target: 0.95),
            cacheStampede     : new CacheStampedeRisk(protectionRequired: true),
            queueDepth        : new QueueDepth(maxDepth: 100000),
            consumerThroughput: new ConsumerThroughput(perSecond: 2000),
            latencyBudget     : new LatencyBudget(p50Ms: 50, p95Ms: 2000, p99Ms: 6000),
            slo               : new Slo(percentage: 99.9),
            failureBudget     : new FailureBudget(minutesPerMonth: 43.8),
        );

        $sim    = FailureSimulation::fromConfig([
                                                    'name'         => 'database-slow',
                                                    'failure_mode' => 'database_slow',
                                                    'description'  => 'Database slow simulation',
                                                    'parameters'   => [],
                                                ]);
        $result = $sim->run($slowModel);

        self::assertTrue($result['violation_detected']);
    }

    public function test_run_all_failure_simulations() : void
    {
        $flow   = new RunFailureSimulations();
        $result = $flow->execute($this->model);

        self::assertGreaterThan(0, $result['total']);
        self::assertArrayHasKey('violations_detected', $result);
        self::assertArrayHasKey('clean', $result);
        self::assertArrayHasKey('simulations', $result);
    }

    public function test_custom_failure_simulations() : void
    {
        $customFailures = [
            [
                'name'         => 'Custom Cache Outage',
                'failure_mode' => 'cache_outage',
                'description'  => 'Custom cache outage',
                'parameters'   => [],
            ],
        ];

        $flow   = new RunFailureSimulations();
        $result = $flow->execute($this->model, $customFailures);

        self::assertSame(1, $result['total']);
    }

    public function test_unknown_failure_mode_returns_no_violation() : void
    {
        $sim    = FailureSimulation::fromConfig([
                                                    'name'         => 'unknown',
                                                    'failure_mode' => 'nonexistent_mode',
                                                    'description'  => 'Unknown failure mode',
                                                    'parameters'   => [],
                                                ]);
        $result = $sim->run($this->model);

        self::assertNull($result['violation']);
        self::assertFalse($result['violation_detected']);
    }

    protected function setUp() : void
    {
        $this->model = new CapacityModel(
            system            : 'test-system',
            traffic           : new RequestsPerSecond(total: 10000, reads: 9000, writes: 1000),
            peakMultiplier    : new PeakTrafficMultiplier(multiplier: 3),
            storage           : new StorageGrowth(growthPerDay: 1000000, averageRecordSizeBytes: 512, retentionDays: 365),
            cacheHitRatio     : new CacheHitRatio(target: 0.95),
            cacheStampede     : new CacheStampedeRisk(protectionRequired: true),
            queueDepth        : new QueueDepth(maxDepth: 100000),
            consumerThroughput: new ConsumerThroughput(perSecond: 2000),
            latencyBudget     : new LatencyBudget(p50Ms: 50, p95Ms: 200, p99Ms: 500),
            slo               : new Slo(percentage: 99.9),
            failureBudget     : new FailureBudget(minutesPerMonth: 43.8),
        );
    }
}
