<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Flows\EstimateFailureBudget;

use Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\CapacityModel;

/**
 * Converts SLO into allowed downtime/error budget.
 *
 * @experimental V3 labs
 */
final class EstimateFailureBudget
{
    /**
     * @return array{
     *     slo_percentage: float,
     *     monthly_downtime_minutes: float,
     *     monthly_downtime_human: string,
     *     yearly_downtime_minutes: float,
     *     yearly_downtime_human: string,
     *     failure_budget_minutes: float,
     *     budget_exhausted_at_incidents: int,
     * }
     */
    public function execute(CapacityModel $model) : array
    {
        $monthlyDowntime = $model->slo->monthlyDowntimeMinutes();
        $yearlyDowntime  = $model->slo->yearlyDowntimeMinutes();
        $budgetMinutes   = $model->failureBudget->minutesPerMonth;

        // How many 1-minute incidents would exhaust the budget
        $budgetExhaustedAt = $budgetMinutes > 0 ? (int) floor($budgetMinutes) : 0;

        return [
            'slo_percentage'                => $model->slo->percentage,
            'monthly_downtime_minutes'      => $monthlyDowntime,
            'monthly_downtime_human'        => $model->sloMonthlyDowntime(),
            'yearly_downtime_minutes'       => $yearlyDowntime,
            'yearly_downtime_human'         => $this->humanMinutes($yearlyDowntime),
            'failure_budget_minutes'        => $budgetMinutes,
            'budget_exhausted_at_incidents' => $budgetExhaustedAt,
        ];
    }

    private function humanMinutes(float $minutes) : string
    {
        if ($minutes < 1) {
            return round($minutes * 60, 1) . ' seconds/year';
        }

        if ($minutes < 60) {
            return round($minutes, 1) . ' minutes/year';
        }

        if ($minutes < 1440) {
            return round($minutes / 60, 1) . ' hours/year';
        }

        return round($minutes / 1440, 1) . ' days/year';
    }
}
