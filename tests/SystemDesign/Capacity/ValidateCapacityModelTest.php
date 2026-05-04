<?php

declare(strict_types=1);

namespace Avax\Components\mDesign\Capacity;

use Avax\Components\Application\SystemDesign\Capacity\System\Flows\ValidateCapacityModel\ValidateCapacityModel;
use Avax\Components\Application\SystemDesign\Capacity\System\PublicSurface\CapacityBudget;
use Avax\Components\Application\SystemDesign\Capacity\System\PublicSurface\CapacityModel;
use Avax\Tests\TestCase;

final class ValidateCapacityModelTest extends TestCase
{
    public function test_validates_within_budget(): void
    {
        $model = new CapacityModel(requestsPerSecond: 5000, queueDepth: 1000);
        $budget = new CapacityBudget(maxRequestsPerSecond: 10000, maxQueueDepth: 50000);

        $flow = new ValidateCapacityModel();
        $report = $flow($model, $budget);

        $this->assertTrue($report->isValid());
    }

    public function test_validates_traffic_violation(): void
    {
        $model = new CapacityModel(requestsPerSecond: 15000, queueDepth: 1000);
        $budget = new CapacityBudget(maxRequestsPerSecond: 10000, maxQueueDepth: 50000);

        $flow = new ValidateCapacityModel();
        $report = $flow($model, $budget);

        $this->assertFalse($report->isValid());
        $this->assertSame('traffic', $report->violations[0]->type);
    }
}
