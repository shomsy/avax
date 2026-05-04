<?php

declare(strict_types=1);

namespace Avax\Tests\SystemDesign\Availability;

use Avax\Components\Application\SystemDesign\Availability\System\Flows\CalculateAvailability\CalculateAvailability;
use Avax\Components\Application\SystemDesign\Availability\System\PublicSurface\AvailabilityModel;
use Avax\Tests\TestCase;

final class AvailabilityModelTest extends TestCase
{
    public function test_availability_model_defines_target(): void
    {
        $model = new AvailabilityModel(
            target: 0.9999,
            regionCount: 3,
            multiRegion: true,
        );

        $this->assertSame(0.9999, $model->target);
        $this->assertTrue($model->multiRegion);
    }

    public function test_calculate_availability_computes_downtime(): void
    {
        $model = new AvailabilityModel(target: 0.99, regionCount: 2);
        $flow = new CalculateAvailability();
        $result = $flow($model);

        $this->assertSame(2, $result['nines']);
        $this->assertSame(5256.0, $result['downtimeMinutesPerYear']);
    }
}
