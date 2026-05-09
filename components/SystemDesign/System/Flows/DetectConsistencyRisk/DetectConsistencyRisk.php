<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Flows\DetectConsistencyRisk;

use Avax\Components\SystemDesign\System\Capabilities\Consistency\ConsistencyModel;

/**
 * Detects consistency risks in an architecture.
 *
 * @experimental V3 labs
 */
final class DetectConsistencyRisk
{
    /**
     * @return list<array{
     *     severity: string,
     *     category: string,
     *     description: string,
     *     path: string,
     *     recommendation: string,
     * }>
     */
    public function execute(ConsistencyModel $model) : array
    {
        $risks = [];

        // Check for strong writes with eventual reads
        $writeProfile = null;
        $readProfile  = null;

        foreach ($model->profiles as $profile) {
            if ($profile->path === 'write_path') {
                $writeProfile = $profile;
            }
            if ($profile->path === 'read_path') {
                $readProfile = $profile;
            }
        }

        if ($writeProfile !== null && $readProfile !== null) {
            if ($writeProfile->model->isGloballyVisible() && $readProfile->model->staleReadRisk() === 'high') {
                $risks[] = [
                    'severity'       => 'high',
                    'category'       => 'read_write_mismatch',
                    'description'    => 'Strong writes combined with eventual reads creates a visibility gap.',
                    'path'           => 'read_path',
                    'recommendation' => 'Consider read-your-writes or session consistency for the read path.',
                ];
            }
        }

        // Check high duplicate risk
        $duplicatePaths = $model->highDuplicateRiskPaths();

        foreach ($duplicatePaths as $path) {
            $risks[] = [
                'severity'       => 'high',
                'category'       => 'duplicate_risk',
                'description'    => "Path '{$path}' has high duplicate risk without deduplication.",
                'path'           => $path,
                'recommendation' => 'Add deduplication or use idempotent consumers.',
            ];
        }

        // Check data loss risk
        $dataLossPaths = $model->dataLossRiskPaths();

        foreach ($dataLossPaths as $path) {
            $risks[] = [
                'severity'       => 'medium',
                'category'       => 'data_loss_risk',
                'description'    => "Path '{$path}' uses a conflict strategy that can lose data.",
                'path'           => $path,
                'recommendation' => 'Consider version vectors or CRDTs if data loss is unacceptable.',
            ];
        }

        // Check replication lag exceeding staleness budget
        if ($model->replicationLag !== null) {
            foreach ($model->stalenessBudgets as $budget) {
                if (! $model->replicationLag->isWithinStalenessBudget($budget->toleranceMs)) {
                    $risks[] = [
                        'severity'       => 'high',
                        'category'       => 'replication_lag_exceeds_budget',
                        'description'    => "Replication lag (p99: {$model->replicationLag->p99Ms}ms) exceeds staleness budget for '{$budget->path}' ({$budget->toleranceMs}ms).",
                        'path'           => $budget->path,
                        'recommendation' => 'Reduce replica count, use stronger consistency, or increase staleness tolerance.',
                    ];
                }
            }
        }

        // Check projection lag
        foreach ($model->projectionLags as $lag) {
            if ($lag->p99Ms > 5000) {
                $risks[] = [
                    'severity'       => 'medium',
                    'category'       => 'projection_lag',
                    'description'    => "Projection '{$lag->projection}' has high p99 lag ({$lag->p99Ms}ms).",
                    'path'           => $lag->projection,
                    'recommendation' => 'Optimize projection pipeline or increase consumer throughput.',
                ];
            }
        }

        return $risks;
    }
}
