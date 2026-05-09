<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Flows\ResolveConflict;

use Avax\Components\SystemDesign\System\Capabilities\Consistency\Conflicts\ConflictResolution;
use Avax\Components\SystemDesign\System\Capabilities\Consistency\ConsistencyModel;

/**
 * Analyzes conflict resolution and estimates impact.
 *
 * @experimental V3 labs
 */
final class ResolveConflict
{
    /**
     * @return array{
     *     conflicts: list<array{
     *         path: string,
     *         strategy: string,
     *         conflicts_per_1000_writes: float,
     *         data_loss_risk: bool,
     *         complexity: string,
     *     }>,
     *     total_data_loss_risk_paths: list<string>,
     *     total_complexity_cost: string,
     * }
     */
    public function execute(ConsistencyModel $model) : array
    {
        $conflicts     = [];
        $dataLossPaths = [];
        $maxComplexity = 'low';

        foreach ($model->conflictResolutions as $resolution) {
            $conflicts[] = [
                'path'                      => $resolution->path,
                'strategy'                  => $resolution->strategy->value,
                'conflicts_per_1000_writes' => $resolution->conflictsPerThousandWrites(),
                'data_loss_risk'            => $resolution->strategy->canLoseData(),
                'complexity'                => $resolution->strategy->complexityCost(),
            ];

            if ($resolution->strategy->canLoseData()) {
                $dataLossPaths[] = $resolution->path;
            }

            $cost          = $resolution->strategy->complexityCost();
            $maxComplexity = $this->maxComplexity($maxComplexity, $cost);
        }

        return [
            'conflicts'                  => $conflicts,
            'total_data_loss_risk_paths' => $dataLossPaths,
            'total_complexity_cost'      => $maxComplexity,
        ];
    }

    private function maxComplexity(string $a, string $b) : string
    {
        $order = ['low' => 0, 'medium' => 1, 'high' => 2];

        return ($order[$b] ?? 0) > ($order[$a] ?? 0) ? $b : $a;
    }
}
