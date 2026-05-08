<?php

declare(strict_types=1);

namespace Avax\Tests\GoldenPathRuntime;

use Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\CapacityModel;
use Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\MessagingModel;
use Avax\Labs\SystemDesignKit\System\Flows\DetectMessagingRisk\DetectMessagingRisk;
use Avax\Labs\SystemDesignKit\System\Flows\EstimateQueuePressure\EstimateQueuePressure;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Proves: V3 models validate the architecture design.
 *
 * V3 is design-time only — it validates the architecture configuration
 * but does not replace runtime behavior.
 */
final class V3ModelValidationTest extends TestCase
{
    private string $capacityConfig;

    private string $messagingConfig;

    #[Test]
    public function capacityModelValidatesArchitecture() : void
    {
        $config = require $this->capacityConfig;
        $model  = CapacityModel::fromConfig($config);

        $result = $model->validate();
        self::assertTrue($result['valid'], 'Capacity model should be valid: ' . implode(', ', $result['errors']));
    }

    #[Test]
    public function capacityModelProducesDerivedCalculations() : void
    {
        $config = require $this->capacityConfig;
        $model  = CapacityModel::fromConfig($config);

        self::assertGreaterThan(0, $model->estimatedPeakRps());
        self::assertGreaterThan(0, $model->estimatedDailyStorageGrowth());
        // estimatedCacheHitsPerSecond produces a positive value
        self::assertGreaterThan(0.0, $model->estimatedCacheHitsPerSecond());
    }

    #[Test]
    public function messagingModelValidatesArchitecture() : void
    {
        $config = require $this->messagingConfig;
        $model  = MessagingModel::fromConfig($config);

        $result = $model->validate();
        self::assertTrue($result['valid'], 'Messaging model should be valid: ' . implode(', ', $result['errors']));
    }

    #[Test]
    public function messagingModelConfirmsCqrsOutboxDlq() : void
    {
        $config = require $this->messagingConfig;
        $model  = MessagingModel::fromConfig($config);

        self::assertTrue($model->usesCqrs());
        self::assertTrue($model->usesOutbox());
        self::assertTrue($model->hasDeadLetterQueue());
        self::assertGreaterThan(0, $model->totalConsumerThroughput());
    }

    #[Test]
    public function detectMessagingRiskReturnsStructuredResults() : void
    {
        $config = require $this->messagingConfig;
        $model  = MessagingModel::fromConfig($config);

        $risks = (new DetectMessagingRisk())->execute($model);

        // Our config is well-configured (outbox, inbox, DLQ, manual ack, retry policy all present)
        // so there should be zero risks
        self::assertCount(0, $risks);
    }

    #[Test]
    public function estimateQueuePressureReturnsExpectedKeys() : void
    {
        $config        = require $this->capacityConfig;
        $capacityModel = CapacityModel::fromConfig($config);

        $pressure = (new EstimateQueuePressure())->execute($capacityModel);

        self::assertArrayHasKey('write_rps', $pressure);
        self::assertArrayHasKey('consumer_throughput', $pressure);
        self::assertArrayHasKey('consumer_count', $pressure);
        self::assertArrayHasKey('required_consumers', $pressure);
        self::assertArrayHasKey('can_handle_load', $pressure);
        self::assertArrayHasKey('queue_utilization', $pressure);
        self::assertArrayHasKey('consumers_underprovisioned', $pressure);
    }

    #[Test]
    public function v3ModelsAreDesignTimeOnly() : void
    {
        $config = require $this->capacityConfig;
        $model  = CapacityModel::fromConfig($config);

        // CapacityModel is readonly — no mutable state
        $ref = new ReflectionClass($model);
        self::assertTrue($ref->isReadonly());
    }

    protected function setUp() : void
    {
        $this->capacityConfig  = __DIR__ . '/../../examples/GoldenPathRuntimeApp/config/capacity.php';
        $this->messagingConfig = __DIR__ . '/../../examples/GoldenPathRuntimeApp/config/messaging.php';
    }
}
