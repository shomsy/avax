<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Flows\ExplainConsistencyTradeoff;

use Avax\Labs\SystemDesignKit\System\Capabilities\Consistency\ConsistencyModel;
use Avax\Labs\SystemDesignKit\System\Capabilities\Consistency\Models\ConsistencyProfile;

/**
 * Explains consistency tradeoffs for each path.
 *
 * @experimental V3 labs
 */
final class ExplainConsistencyTradeoff
{
    /**
     * @return array{
     *     system: string,
     *     total_latency_overhead_ms: int,
     *     paths: list<array{
     *         path: string,
     *         model: string,
     *         overhead_ms: int,
     *         stale_read_risk: string,
     *         staleness_tolerance_ms: int,
     *         tradeoff: string,
     *     }>,
     *     risks: array{
     *         high_stale_read_paths: list<string>,
     *         high_duplicate_risk_paths: list<string>,
     *         data_loss_risk_paths: list<string>,
     *     },
     * }
     */
    public function execute(ConsistencyModel $model) : array
    {
        $paths = [];

        foreach ($model->profiles as $profile) {
            $paths[] = [
                'path'                   => $profile->path,
                'model'                  => $profile->model->value,
                'overhead_ms'            => $profile->estimatedLatencyOverheadMs(),
                'stale_read_risk'        => $profile->model->staleReadRisk(),
                'staleness_tolerance_ms' => $profile->stalenessToleranceMs,
                'tradeoff'               => $this->describeTradeoff($profile),
            ];
        }

        return [
            'system'                    => $model->system,
            'total_latency_overhead_ms' => $model->totalEstimatedLatencyOverheadMs(),
            'paths'                     => $paths,
            'risks'                     => [
                'high_stale_read_paths'     => $model->highStaleReadRiskPaths(),
                'high_duplicate_risk_paths' => $model->highDuplicateRiskPaths(),
                'data_loss_risk_paths'      => $model->dataLossRiskPaths(),
            ],
        ];
    }

    private function describeTradeoff(ConsistencyProfile $profile) : string
    {
        $model = $profile->model;

        if ($model->isGloballyVisible()) {
            return "Strong consistency ensures all readers see the latest write but adds ~{$model->estimatedOverheadMs()}ms overhead per write.";
        }

        if ($model->guaranteesReadYourWrites()) {
            return "Session/read-your-writes consistency ensures the writer sees their own writes but replicas may lag by up to {$profile->stalenessToleranceMs}ms for other readers.";
        }

        if ($model->staleReadRisk() === 'high') {
            return "Eventual consistency minimizes write latency but readers may see stale data for up to {$profile->stalenessToleranceMs}ms.";
        }

        return "{$model->value} consistency provides {$model->staleReadRisk()} stale-read risk with ~{$model->estimatedOverheadMs()}ms overhead.";
    }
}
