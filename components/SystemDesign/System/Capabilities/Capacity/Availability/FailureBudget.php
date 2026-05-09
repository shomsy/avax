<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Capabilities\Capacity\Availability;

/**
 * Failure budget — how much downtime is allowed per month.
 *
 * @experimental V3 labs
 */
final readonly class FailureBudget
{
    public function __construct(
        public float $minutesPerMonth,
    ) {}

    /**
     * Check if an incident fits within the remaining budget.
     */
    public function canAbsorbIncident(float $incidentMinutes, float $alreadyUsedMinutes) : bool
    {
        return ($alreadyUsedMinutes + $incidentMinutes) <= $this->minutesPerMonth;
    }

    /**
     * Remaining budget after incidents.
     */
    public function remainingMinutes(float $alreadyUsedMinutes) : float
    {
        return max(0.0, $this->minutesPerMonth - $alreadyUsedMinutes);
    }

    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate() : array
    {
        $errors = [];

        if ($this->minutesPerMonth <= 0) {
            $errors[] = 'failure_budget_minutes_per_month must be positive.';
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }
}
