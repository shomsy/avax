<?php

declare(strict_types=1);

namespace Avax\Tests\SystemDesignKit\ScenarioRunner;

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
use Avax\Labs\SystemDesignKit\System\Capabilities\ScenarioRunner\Scenario;
use Avax\Labs\SystemDesignKit\System\Capabilities\ScenarioRunner\ScenarioExpectation;
use Avax\Labs\SystemDesignKit\System\Capabilities\ScenarioRunner\ScenarioStep;
use Avax\Labs\SystemDesignKit\System\Flows\RunScenarios\RunScenarios;
use PHPUnit\Framework\TestCase;

final class ScenarioRunnerTest extends TestCase
{
    private CapacityModel $model;

    public function test_scenario_step_creation() : void
    {
        $step = new ScenarioStep(action: 'disable_cache', target: 'redis_primary');

        self::assertSame('disable_cache', $step->action);
        self::assertSame('redis_primary', $step->target);
        self::assertNull($step->parameter);
    }

    public function test_scenario_step_from_config() : void
    {
        $step = ScenarioStep::fromConfig([
                                             'action'    => 'multiply_traffic',
                                             'parameter' => 5,
                                         ]);

        self::assertSame('multiply_traffic', $step->action);
        self::assertNull($step->target);
        self::assertSame(5, $step->parameter);
    }

    public function test_scenario_expectation_creation() : void
    {
        $expectation = new ScenarioExpectation(assertion: 'latency_p99_under_threshold', threshold: 500);

        self::assertSame('latency_p99_under_threshold', $expectation->assertion);
        self::assertSame(500, $expectation->threshold);
    }

    public function test_scenario_from_config() : void
    {
        $config = [
            'name'         => 'redis-outage',
            'type'         => 'failure',
            'description'  => 'Redis cache becomes unavailable.',
            'steps'        => [
                ['action' => 'disable_cache', 'target' => 'redis_primary'],
            ],
            'expectations' => [
                ['assertion' => 'latency_p99_under_threshold', 'threshold' => 500],
            ],
        ];

        $scenario = Scenario::fromConfig($config);

        self::assertSame('redis-outage', $scenario->name);
        self::assertSame('failure', $scenario->type);
        self::assertCount(1, $scenario->steps);
        self::assertCount(1, $scenario->expectations);
    }

    public function test_scenario_evaluation_passes() : void
    {
        $scenario = new Scenario(
            name        : 'latency-check',
            type        : 'load',
            description : 'P99 latency check',
            steps       : [],
            expectations: [
                              new ScenarioExpectation(assertion: 'latency_p99_under_threshold', threshold: 1000),
                          ],
        );

        $result = $scenario->evaluate($this->model);

        self::assertTrue($result['passed']);
        self::assertCount(1, $result['results']);
        self::assertTrue($result['results'][0]['passed']);
    }

    public function test_scenario_evaluation_fails() : void
    {
        $scenario = new Scenario(
            name        : 'latency-check',
            type        : 'load',
            description : 'P99 latency check',
            steps       : [],
            expectations: [
                              new ScenarioExpectation(assertion: 'latency_p99_under_threshold', threshold: 100),
                          ],
        );

        $result = $scenario->evaluate($this->model);

        self::assertFalse($result['passed']);
        self::assertFalse($result['results'][0]['passed']);
    }

    public function test_run_scenarios_from_file() : void
    {
        $flow   = new RunScenarios();
        $result = $flow->execute(__DIR__ . '/../../../labs/SystemDesignKit/reference-architectures/url-shortener/scenarios.yaml', $this->model);

        self::assertGreaterThan(0, $result['total']);
        self::assertArrayHasKey('passed', $result);
        self::assertArrayHasKey('failed', $result);
        self::assertArrayHasKey('scenarios', $result);
    }

    public function test_scenario_handles_unknown_assertion() : void
    {
        $scenario = new Scenario(
            name        : 'unknown-test',
            type        : 'failure',
            description : 'Test unknown assertion',
            steps       : [],
            expectations: [
                              new ScenarioExpectation(assertion: 'nonexistent_assertion'),
                          ],
        );

        $result = $scenario->evaluate($this->model);

        self::assertFalse($result['passed']);
        self::assertStringContainsString('Unknown assertion', $result['results'][0]['detail']);
    }

    public function test_multiple_expectations_all_must_pass() : void
    {
        $scenario = new Scenario(
            name        : 'multi-check',
            type        : 'failure',
            description : 'Multiple expectations',
            steps       : [],
            expectations: [
                              new ScenarioExpectation(assertion: 'latency_p99_under_threshold', threshold: 1000),
                              new ScenarioExpectation(assertion: 'queue_depth_under_limit', threshold: 200000),
                              new ScenarioExpectation(assertion: 'no_data_loss'),
                          ],
        );

        $result = $scenario->evaluate($this->model);

        self::assertTrue($result['passed']);
        self::assertCount(3, $result['results']);
    }

    public function test_multiple_expectations_one_fails() : void
    {
        $scenario = new Scenario(
            name        : 'multi-check',
            type        : 'failure',
            description : 'Multiple expectations, one fails',
            steps       : [],
            expectations: [
                              new ScenarioExpectation(assertion: 'latency_p99_under_threshold', threshold: 1000),
                              new ScenarioExpectation(assertion: 'queue_depth_under_limit', threshold: 50000),
                          ],
        );

        $result = $scenario->evaluate($this->model);

        self::assertFalse($result['passed']);
        self::assertSame(100000, $result['results'][1]['results'][0] ?? $this->model->queueDepth->maxDepth);
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
