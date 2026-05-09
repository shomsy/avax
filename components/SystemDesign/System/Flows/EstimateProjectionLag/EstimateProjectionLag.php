<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Flows\EstimateProjectionLag;

use Avax\Components\SystemDesign\System\Capabilities\Consistency\ConsistencyModel;

/**
 * Estimates projection lag across all projections.
 *
 * @experimental V3 labs
 */
final class EstimateProjectionLag
{
    /**
     * @return array{
     *     projections: list<array{
     *         name: string,
     *         expected_ms: int,
     *         p95_ms: int,
     *         p99_ms: int,
     *         within_budget: bool,
     *     }>,
     *     max_p99_ms: int,
     *     projection_count: int,
     * }
     */
    public function execute(ConsistencyModel $model, int $freshnessThresholdMs = 1000) : array
    {
        $projections = [];
        $maxP99      = 0;

        foreach ($model->projectionLags as $lag) {
            $projections[] = [
                'name'          => $lag->projection,
                'expected_ms'   => $lag->expectedMs,
                'p95_ms'        => $lag->p95Ms,
                'p99_ms'        => $lag->p99Ms,
                'within_budget' => $lag->isFresh($lag->p99Ms, $freshnessThresholdMs),
            ];

            if ($lag->p99Ms > $maxP99) {
                $maxP99 = $lag->p99Ms;
            }
        }

        return [
            'projections'      => $projections,
            'max_p99_ms'       => $maxP99,
            'projection_count' => count($projections),
        ];
    }
}
