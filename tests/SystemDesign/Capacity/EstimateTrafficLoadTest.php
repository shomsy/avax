<?php

declare(strict_types=1);

namespace Avax\Components\mDesign\Capacity;

use Avax\Components\Application\SystemDesign\Capacity\System\Flows\EstimateTrafficLoad\EstimateTrafficLoad;
use Avax\Components\Application\SystemDesign\Capacity\System\PublicSurface\CapacityModel;
use Avax\Tests\TestCase;

final class EstimateTrafficLoadTest extends TestCase
{
    public function test_estimates_peak_traffic(): void
    {
        $model = new CapacityModel(
            requestsPerSecond: 1000,
            peakTrafficMultiplier: 2,
            burstWindow: 60,
            readWriteRatio: 3.0,
            fanoutSize: 1,
        );

        $flow = new EstimateTrafficLoad();
        $result = $flow($model);

        $this->assertSame(2000, $result['peakRequestsPerSecond']);
        $this->assertSame(120000, $result['burstRequests']);
    }

    public function test_estimates_read_write_split(): void
    {
        $model = new CapacityModel(
            requestsPerSecond: 1000,
            readWriteRatio: 3.0,
            peakTrafficMultiplier: 1,
            fanoutSize: 1,
        );

        $flow = new EstimateTrafficLoad();
        $result = $flow($model);

        $this->assertSame(750, $result['readsPerSecond']);
        $this->assertSame(250, $result['writesPerSecond']);
    }
}
