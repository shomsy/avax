<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Simulation\System\Flows\RunSimulation;

use Avax\Components\SystemDesign\Simulation\System\PublicSurface\SimulationConfig;

final readonly class RunSimulation
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(SimulationConfig $config, callable $fn): array
    {
        $results = [];

        for ($i = 0; $i < $config->warmup; $i++) {
            $fn();
        }

        for ($i = 0; $i < $config->iterations; $i++) {
            $start = hrtime(true);
            $fn();
            $duration = (hrtime(true) - $start) / 1e6;
            $results[] = $duration;
        }

        sort($results);

        return [
            'iterations' => $config->iterations,
            'p50Ms' => $results[(int)(count($results) * 0.50)] ?? 0,
            'p95Ms' => $results[(int)(count($results) * 0.95)] ?? 0,
            'p99Ms' => $results[(int)(count($results) * 0.99)] ?? 0,
        ];
    }
}
