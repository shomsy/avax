<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Flows\EstimateLatencyBudget;

use Avax\Components\SystemDesign\System\Capabilities\Capacity\CapacityModel;

/**
 * Splits latency budget across HTTP, cache, DB, queue, external calls.
 *
 * @experimental V3 labs
 */
final class EstimateLatencyBudget
{
    /**
     * Default budget allocation percentages.
     */
    private const ALLOCATION
        = [
            'http_routing'   => 0.05,
            'cache'          => 0.10,
            'database'       => 0.50,
            'serialization'  => 0.10,
            'external_calls' => 0.15,
            'overhead'       => 0.10,
        ];

    /**
     * @return array{
     *     p50_budget_ms: int,
     *     p95_budget_ms: int,
     *     p99_budget_ms: int,
     *     allocation: array<string, array{p50_ms: float, p95_ms: float, p99_ms: float}>,
     *     budget_valid: bool,
     *     budget_violations: list<string>,
     * }
     */
    public function execute(CapacityModel $model) : array
    {
        $p50 = $model->latencyBudget->p50Ms;
        $p95 = $model->latencyBudget->p95Ms;
        $p99 = $model->latencyBudget->p99Ms;

        $allocation = [];
        foreach (self::ALLOCATION as $component => $share) {
            $allocation[$component] = [
                'p50_ms' => round($p50 * $share, 2),
                'p95_ms' => round($p95 * $share, 2),
                'p99_ms' => round($p99 * $share, 2),
            ];
        }

        $budgetValid = $p50 <= $p95 && $p95 <= $p99;

        return [
            'p50_budget_ms'     => $p50,
            'p95_budget_ms'     => $p95,
            'p99_budget_ms'     => $p99,
            'allocation'        => $allocation,
            'budget_valid'      => $budgetValid,
            'budget_violations' => $budgetValid ? [] : ['Latency percentiles are not ordered: p50 <= p95 <= p99'],
        ];
    }
}
