<?php

declare(strict_types=1);

namespace Avax\Tests\SystemDesign\LoadModel;

use Avax\Components\SystemDesign\LoadModel\System\Flows\EstimateLoad\EstimateLoad;
use Avax\Components\SystemDesign\LoadModel\System\PublicSurface\LoadModel;
use Avax\Tests\TestCase;

final class LoadModelTest extends TestCase
{
    public function test_load_model_defines_parameters(): void
    {
        $model = new LoadModel(
            concurrentUsers: 500,
            requestsPerUser: 20,
            thinkTimeSeconds: 2.0,
            rampUpSeconds: 120,
        );

        $this->assertSame(500, $model->concurrentUsers);
        $this->assertSame(20, $model->requestsPerUser);
    }

    public function test_estimate_load_calculates_rps(): void
    {
        $model = new LoadModel(
            concurrentUsers: 100,
            requestsPerUser: 10,
            thinkTimeSeconds: 1.0,
        );

        $flow = new EstimateLoad();
        $result = $flow($model);

        $this->assertSame(1000, $result['stableRequestsPerSecond']);
        $this->assertSame(1500, $result['peakRequestsPerSecond']);
    }
}
