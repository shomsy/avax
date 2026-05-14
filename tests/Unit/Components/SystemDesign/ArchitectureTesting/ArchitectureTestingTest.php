<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\SystemDesign\ArchitectureTesting;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\SystemDesign\System\Capabilities\ArchitectureTesting\ArchitectureTest;
use Avax\Components\SystemDesign\System\Capabilities\Capacity\Availability\FailureBudget;
use Avax\Components\SystemDesign\System\Capabilities\Capacity\Availability\Slo;
use Avax\Components\SystemDesign\System\Capabilities\Capacity\Cache\CacheHitRatio;
use Avax\Components\SystemDesign\System\Capabilities\Capacity\Cache\CacheStampedeRisk;
use Avax\Components\SystemDesign\System\Capabilities\Capacity\CapacityModel;
use Avax\Components\SystemDesign\System\Capabilities\Capacity\Latency\LatencyBudget;
use Avax\Components\SystemDesign\System\Capabilities\Capacity\Queue\ConsumerThroughput;
use Avax\Components\SystemDesign\System\Capabilities\Capacity\Queue\QueueDepth;
use Avax\Components\SystemDesign\System\Capabilities\Capacity\Storage\StorageGrowth;
use Avax\Components\SystemDesign\System\Capabilities\Capacity\Traffic\PeakTrafficMultiplier;
use Avax\Components\SystemDesign\System\Capabilities\Capacity\Traffic\RequestsPerSecond;
use Avax\Components\SystemDesign\System\Capabilities\Messaging\DeadLetters\DeadLetterQueue;
use Avax\Components\SystemDesign\System\Capabilities\Messaging\MessagingModel;
use Avax\Components\SystemDesign\System\Capabilities\Messaging\Outbox\Outbox;
use Avax\Components\SystemDesign\System\Capabilities\Messaging\Retry\RetryPolicy;
use Avax\Components\SystemDesign\System\Capabilities\Messaging\Types\Message;
use Avax\Components\SystemDesign\System\Capabilities\Messaging\Types\MessageType;
use Avax\Components\SystemDesign\System\Capabilities\SchemaValidation\NativeYamlParser;
use Avax\Components\SystemDesign\System\Flows\RunArchitectureTests\RunArchitectureTests;
use PHPUnit\Framework\TestCase;

final class ArchitectureTestingTest extends TestCase
{
    private CapacityModel  $capacity;
    private MessagingModel $messaging;

    public function test_architecture_test_creation() : void
    {
        $test = new ArchitectureTest(
            name       : 'test-idempotency',
            type       : 'idempotency',
            description: 'Test idempotency check',
            severity   : 'critical',
            parameters : ['expected' => 'idempotent'],
        );

        self::assertSame('test-idempotency', $test->name);
        self::assertSame('critical', $test->severity);
    }

    public function test_architecture_test_from_config() : void
    {
        $config = [
            'name'        => 'hot-path-test',
            'type'        => 'hot_path',
            'description' => 'Hot path test',
            'severity'    => 'high',
            'parameters'  => ['path' => 'redirect', 'excluded' => ['analytics']],
        ];

        $test = ArchitectureTest::fromConfig($config);

        self::assertSame('hot-path-test', $test->name);
        self::assertSame('hot_path', $test->type);
        self::assertSame('high', $test->severity);
    }

    public function test_idempotency_test_passes() : void
    {
        $test = new ArchitectureTest(
            name       : 'idempotency',
            type       : 'idempotency',
            description: 'Idempotency test',
            severity   : 'critical',
            parameters : ['expected' => 'idempotent'],
        );

        $result = $test->evaluate($this->capacity, $this->messaging);

        self::assertTrue($result['passed']);
    }

    public function test_idempotency_test_fails_without_messaging() : void
    {
        $test = new ArchitectureTest(
            name       : 'idempotency',
            type       : 'idempotency',
            description: 'Idempotency test',
            severity   : 'critical',
            parameters : ['expected' => 'idempotent'],
        );

        $result = $test->evaluate($this->capacity, null);

        self::assertFalse($result['passed']);
    }

    public function test_cache_stampede_test_passes() : void
    {
        $test = new ArchitectureTest(
            name       : 'cache-stampede',
            type       : 'cache',
            description: 'Cache stampede test',
            severity   : 'high',
            parameters : ['expected' => 'stampede_protection'],
        );

        $result = $test->evaluate($this->capacity);

        self::assertTrue($result['passed']);
    }

    public function test_dead_letter_test_passes() : void
    {
        $test = new ArchitectureTest(
            name       : 'dlq',
            type       : 'dead_letter',
            description: 'Dead letter test',
            severity   : 'medium',
            parameters : ['required' => ['dead_letter']],
        );

        $result = $test->evaluate($this->capacity, $this->messaging);

        self::assertTrue($result['passed']);
    }

    public function test_observability_test_passes() : void
    {
        $test = new ArchitectureTest(
            name       : 'observability',
            type       : 'observability',
            description: 'Observability test',
            severity   : 'medium',
            parameters : ['required' => ['alert_channel']],
        );

        $result = $test->evaluate($this->capacity, $this->messaging);

        self::assertTrue($result['passed']);
    }

    public function test_run_architecture_tests_from_file() : void
    {
        $yamlParser = new NativeYamlParser(filesystem: new Filesystem());
        $flow       = new RunArchitectureTests(yamlParser: $yamlParser);
        $result     = $flow->execute(
            __DIR__ . '/../../../../../components/SystemDesign/reference-architectures/url-shortener/architecture-tests.yaml',
            $this->capacity,
            $this->messaging,
        );

        self::assertGreaterThan(0, $result['total']);
        self::assertArrayHasKey('passed', $result);
        self::assertArrayHasKey('failed', $result);
        self::assertArrayHasKey('critical_failures', $result);
    }

    public function test_resilience_test_passes() : void
    {
        $test = new ArchitectureTest(
            name       : 'resilience',
            type       : 'resilience',
            description: 'Resilience test',
            severity   : 'high',
            parameters : ['expected' => 'load_shedding_policy'],
        );

        $result = $test->evaluate($this->capacity);

        self::assertTrue($result['passed']);
    }

    protected function setUp() : void
    {
        $this->capacity = new CapacityModel(
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

        $this->messaging = new MessagingModel(
            system         : 'test-system',
            messages       : [
                                 new Message(name: 'TestEvent', type: MessageType::Event, idempotent: true, maxRetries: 3, timeoutMs: 5000),
                             ],
            consumers      : [],
            outbox         : new Outbox(enabled: true, pollIntervalMs: 100, maxBatchSize: 100, retentionHours: 24),
            deadLetterQueue: new DeadLetterQueue(enabled: true, maxRetries: 5, reprocessingWindowHours: 72, alertChannel: 'ops-alerts'),
            retryPolicy    : new RetryPolicy(maxRetries: 3, backoffStrategy: 'exponential', initialDelayMs: 100, maxDelayMs: 30000),
        );
    }
}
