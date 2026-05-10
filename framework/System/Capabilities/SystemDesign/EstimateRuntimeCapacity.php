<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\SystemDesign;

use Avax\Framework\System\Capabilities\SystemDesign\Foundation\CapacityRecommendation;

final class EstimateRuntimeCapacity
{
    /**
     * @param array<string, mixed> $runtimeConfig
     * @return list<CapacityRecommendation>
     */
    public function recommend(array $runtimeConfig = []): array
    {
        $recommendations = [];

        $maxWorkers = $runtimeConfig['max_workers'] ?? 4;
        $avgLatency = $runtimeConfig['avg_latency_ms'] ?? 50;
        $targetRps = $runtimeConfig['target_requests_per_second'] ?? 100;

        if ($avgLatency > 0 && $targetRps > 0) {
            $neededWorkers = (int) ceil(($targetRps * $avgLatency) / 1000);
            $recommendations[] = new CapacityRecommendation(
                'worker_count',
                "Recommend {$neededWorkers} workers for {$targetRps} RPS at {$avgLatency}ms latency.",
                'Based on Little\'s Law: workers = RPS * latency / 1000.',
                ['current_workers' => $maxWorkers, 'recommended_workers' => $neededWorkers],
            );
        }

        $poolSize = $runtimeConfig['db_pool_size'] ?? 10;
        $recommendations[] = new CapacityRecommendation(
            'connection_pool',
            "Current DB pool size: {$poolSize}. Ensure pool >= workers for connection-heavy workloads.",
            'Connection pool should not be the bottleneck.',
            ['pool_size' => $poolSize],
        );

        $cacheStrategy = $runtimeConfig['cache_strategy'] ?? 'none';
        if ($cacheStrategy === 'none') {
            $recommendations[] = new CapacityRecommendation(
                'cache',
                'No caching strategy configured. Consider adding response or query caching.',
                'Caching reduces backend load significantly for read-heavy workloads.',
            );
        }

        return $recommendations;
    }
}
