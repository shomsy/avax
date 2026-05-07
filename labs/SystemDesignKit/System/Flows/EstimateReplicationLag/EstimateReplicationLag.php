<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Flows\EstimateReplicationLag;

use Avax\Labs\SystemDesignKit\System\Capabilities\Consistency\ConsistencyModel;

/**
 * Estimates replication lag and staleness impact.
 *
 * @experimental V3 labs
 */
final class EstimateReplicationLag
{
    /**
     * @return array{
     *     has_replication: bool,
     *     expected_ms: int,
     *     p95_ms: int,
     *     p99_ms: int,
     *     replica_count: int,
     *     topology: string,
     *     worst_case_staleness_ms: int,
     *     budgets_exceeded: list<array{
     *         path: string,
     *         budget_ms: int,
     *         exceeded_by_ms: int,
     *     }>,
     * }
     */
    public function execute(ConsistencyModel $model) : array
    {
        if ($model->replicationLag === null) {
            return [
                'has_replication'         => false,
                'expected_ms'             => 0,
                'p95_ms'                  => 0,
                'p99_ms'                  => 0,
                'replica_count'           => 0,
                'topology'                => '',
                'worst_case_staleness_ms' => 0,
                'budgets_exceeded'        => [],
            ];
        }

        $replication     = $model->replicationLag;
        $budgetsExceeded = [];

        foreach ($model->stalenessBudgets as $budget) {
            if (! $replication->isWithinStalenessBudget($budget->toleranceMs)) {
                $budgetsExceeded[] = [
                    'path'           => $budget->path,
                    'budget_ms'      => $budget->toleranceMs,
                    'exceeded_by_ms' => $replication->p99Ms - $budget->toleranceMs,
                ];
            }
        }

        return [
            'has_replication'         => true,
            'expected_ms'             => $replication->expectedMs,
            'p95_ms'                  => $replication->p95Ms,
            'p99_ms'                  => $replication->p99Ms,
            'replica_count'           => $replication->replicaCount,
            'topology'                => $replication->topology,
            'worst_case_staleness_ms' => $replication->worstCaseStalenessMs(),
            'budgets_exceeded'        => $budgetsExceeded,
        ];
    }
}
