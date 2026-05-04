<?php

declare(strict_types=1);

namespace Avax\Components\mDesign\Capacity;

use Avax\Components\SystemDesign\Capacity\System\Flows\ValidateCapacityModel\ValidateCapacityModel;
use Avax\Components\SystemDesign\Capacity\System\PublicSurface\CapacityBudget;
use Avax\Components\SystemDesign\Capacity\System\PublicSurface\CapacityModel;
use Avax\Components\SystemDesign\Capacity\System\PublicSurface\CapacityReport;
use Avax\Tests\TestCase;

final class CapacityModelTest extends TestCase
{
    public function test_capacity_model_can_be_created(): void
    {
        $model = new CapacityModel(
            requestsPerSecond: 1000,
            readsPerSecond: 800,
            writesPerSecond: 200,
            readWriteRatio: 4.0,
            peakTrafficMultiplier: 2,
            burstWindow: 60,
            fanoutSize: 10,
            averageObjectSize: 1024,
            retentionDays: 30,
            cacheHitRatio: 0.8,
            queueDepth: 1000,
            consumerCount: 5,
        );

        $this->assertSame(1000, $model->requestsPerSecond);
        $this->assertSame(800, $model->readsPerSecond);
        $this->assertSame(200, $model->writesPerSecond);
        $this->assertSame(4.0, $model->readWriteRatio);
    }

    public function test_capacity_report_validates_violations(): void
    {
        $model = new CapacityModel(requestsPerSecond: 20000);
        $budget = new CapacityBudget(maxRequestsPerSecond: 10000);

        $validate = new ValidateCapacityModel();
        $report = $validate($model, $budget);

        $this->assertFalse($report->isValid());
        $this->assertCount(1, $report->violations);
    }

    public function test_capacity_budget_defines_limits(): void
    {
        $budget = new CapacityBudget(
            maxRequestsPerSecond: 5000,
            maxStorageMb: 100000,
            maxQueueDepth: 50000,
            maxLatencyMs: 100,
            availability: 0.9999,
        );

        $this->assertSame(5000, $budget->maxRequestsPerSecond);
        $this->assertSame(0.9999, $budget->availability);
    }
}
