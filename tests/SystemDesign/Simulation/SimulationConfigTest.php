<?php

declare(strict_types=1);

namespace Avax\Tests\SystemDesign\Simulation;

use Avax\Components\Application\SystemDesign\Simulation\System\PublicSurface\SimulationConfig;
use Avax\Tests\TestCase;

final class SimulationConfigTest extends TestCase
{
    public function test_simulation_config_defaults(): void
    {
        $config = new SimulationConfig();

        $this->assertSame(1000, $config->iterations);
        $this->assertSame(100, $config->warmup);
        $this->assertFalse($config->parallel);
    }

    public function test_simulation_config_can_be_customized(): void
    {
        $config = new SimulationConfig(
            iterations: 5000,
            warmup: 500,
            parallel: true,
        );

        $this->assertSame(5000, $config->iterations);
        $this->assertTrue($config->parallel);
    }
}
